<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Infrastructure\Mail\Mailables\VerificationMailable;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

describe('POST /api/auth/email-verification/send', function () {
    describe('Happy path', function () {
        it('sends verification email to unverified user', function () {
            Mail::fake();

            $user = UserModel::factory()->unverified()->create([
                'email' => 'unverified@example.com',
            ]);

            $response = $this->postJson('/api/auth/email-verification/send', [
                'email' => 'unverified@example.com',
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data',
                ])
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'If your email is registered, you will receive a verification link.',
                ]);

            Mail::assertQueued(VerificationMailable::class, function ($mail) use ($user) {
                return $mail->user->getEmail()->getValue() === $user->email;
            });
        });

        it('returns already verified message for verified user', function () {
            Mail::fake();

            UserModel::factory()->create([
                'email' => 'verified@example.com',
                'email_verified_at' => now(),
            ]);

            $response = $this->postJson('/api/auth/email-verification/send', [
                'email' => 'verified@example.com',
            ]);

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'Your email is already verified.',
                ]);

            Mail::assertNotQueued(VerificationMailable::class);
        });

        it('returns same message for non-existent email (no enumeration)', function () {
            Mail::fake();

            $response = $this->postJson('/api/auth/email-verification/send', [
                'email' => 'nonexistent@example.com',
            ]);

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'If your email is registered, you will receive a verification link.',
                ]);

            Mail::assertNotQueued(VerificationMailable::class);
        });
    });

    describe('HTTP request validation', function () {
        it('validates email is required', function () {
            $response = $this->postJson('/api/auth/email-verification/send', []);

            $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors',
                ])
                ->assertJsonFragment([
                    'status' => 422,
                    'message' => 'One or more validation errors occurred.',
                ])
                ->assertJsonPath('errors.email', fn ($errors) => in_array('The email field is required.', $errors));
        });

        it('validates email format', function () {
            $response = $this->postJson('/api/auth/email-verification/send', [
                'email' => 'invalid-email',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.email', fn ($errors) => in_array('The email field must be a valid email address.', $errors));
        });
    });

    describe('Business rules', function () {
        it('invalidates old tokens when sending new verification email', function () {
            Mail::fake();

            $user = UserModel::factory()->unverified()->create([
                'email' => 'user@example.com',
            ]);

            // Create an existing token
            $oldToken = EmailVerificationTokenModel::create([
                'user_id' => $user->id,
                'token' => 'old-token-12345678901234567890123456789012345678901234567890',
                'expires_at' => now()->addMinutes(15),
            ]);

            // Request new verification email
            $response = $this->postJson('/api/auth/email-verification/send', [
                'email' => 'user@example.com',
            ]);

            $response->assertStatus(200);

            // Old token should be deleted
            $this->assertDatabaseMissing('email_verification_tokens', [
                'id' => $oldToken->id,
            ]);

            // New token should exist
            $this->assertDatabaseHas('email_verification_tokens', [
                'user_id' => $user->id,
            ]);

            // Only one token should exist for this user
            expect(EmailVerificationTokenModel::where('user_id', $user->id)->count())->toBe(1);
        });
    });
});
