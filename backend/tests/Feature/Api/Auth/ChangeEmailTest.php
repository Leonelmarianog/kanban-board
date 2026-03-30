<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Infrastructure\Mail\Mailables\EmailChangeNotificationMailable;
use Modules\Infrastructure\Mail\Mailables\EmailChangeVerificationMailable;
use Modules\Infrastructure\Persistence\Models\EmailChangeTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

describe('POST /api/auth/email-change', function () {
    describe('Happy path', function () {
        it('initiates email change successfully', function () {
            Mail::fake();

            $user = UserModel::factory()->create([
                'email' => 'old@example.com',
            ]);

            $response = $this->actingAs($user)->postJson('/api/auth/email-change', [
                'new_email' => 'new@example.com',
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data',
                ])
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'A verification email has been sent to your new email address.',
                ]);

            // Verify token was created
            $tokenRecord = EmailChangeTokenModel::where('user_id', $user->id)->first();
            expect($tokenRecord)->not->toBeNull();
            expect($tokenRecord->new_email)->toBe('new@example.com');
            expect($tokenRecord->current_email)->toBe('old@example.com');

            // Verify emails were queued
            Mail::assertQueued(EmailChangeVerificationMailable::class);
            Mail::assertQueued(EmailChangeNotificationMailable::class);
        });

        it('cancels existing pending request when creating new one', function () {
            Mail::fake();

            $user = UserModel::factory()->create([
                'email' => 'old@example.com',
            ]);

            // Create existing pending request
            $existingToken = EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => $user->email,
                'new_email' => 'first@example.com',
                'token' => Str::random(64),
                'expires_at' => now()->addHour(),
            ]);

            $response = $this->actingAs($user)->postJson('/api/auth/email-change', [
                'new_email' => 'new@example.com',
            ]);

            $response->assertStatus(200);

            // Old token should be deleted
            $oldToken = EmailChangeTokenModel::find($existingToken->id);
            expect($oldToken)->toBeNull();

            // New token should exist
            $newToken = EmailChangeTokenModel::where('user_id', $user->id)->first();
            expect($newToken)->not->toBeNull()
                ->and($newToken->new_email)->toBe('new@example.com');
        });
    });

    describe('HTTP request validation', function () {
        it('validates new_email is required', function () {
            $user = UserModel::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/auth/email-change', []);

            $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors',
                ])
                ->assertJsonPath('errors.new_email', fn ($errors) => in_array('The new email field is required.', $errors));
        });

        it('validates new_email is valid email', function () {
            $user = UserModel::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/auth/email-change', [
                'new_email' => 'invalid-email',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.new_email', fn ($errors) => in_array('The new email field must be a valid email address.', $errors));
        });

        it('validates new_email max length', function () {
            $user = UserModel::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/auth/email-change', [
                'new_email' => str_repeat('a', 250).'@example.com',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.new_email', fn ($errors) => in_array('The new email field must not be greater than 255 characters.', $errors));
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/auth/email-change', [
                'new_email' => 'new@example.com',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Business rules', function () {
        it('returns 422 when new email equals current email', function () {
            $user = UserModel::factory()->create([
                'email' => 'same@example.com',
            ]);

            $response = $this->actingAs($user)->postJson('/api/auth/email-change', [
                'new_email' => 'same@example.com',
            ]);

            $response->assertStatus(422)
                ->assertJsonFragment([
                    'status' => 422,
                    'message' => 'The new email must be different from your current email.',
                ]);
        });

        it('returns 409 when email is already in use by another user', function () {
            $user = UserModel::factory()->create([
                'email' => 'user1@example.com',
            ]);

            UserModel::factory()->create([
                'email' => 'user2@example.com',
            ]);

            $response = $this->actingAs($user)->postJson('/api/auth/email-change', [
                'new_email' => 'user2@example.com',
            ]);

            $response->assertStatus(409)
                ->assertJsonFragment([
                    'status' => 409,
                ]);
        });
    });
});
