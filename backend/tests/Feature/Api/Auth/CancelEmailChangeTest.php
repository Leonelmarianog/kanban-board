<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Infrastructure\Persistence\Models\EmailChangeTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

function generateTestTokenForCancel(): string
{
    return Str::random(64);
}

function generateTestSignatureForCancel(string $token, int $expires): string
{
    $frontendUrl = config('app.frontend_url').'/email-change/cancel';
    $urlToSign = $frontendUrl.'?token='.$token;

    return hash_hmac('sha256', "{$urlToSign}::{$expires}", config('app.key'));
}

describe('POST /api/auth/email-change/cancel', function () {
    describe('Happy path', function () {
        it('cancels pending email change successfully', function () {
            $user = UserModel::factory()->create([
                'email' => 'old@example.com',
            ]);

            $token = generateTestTokenForCancel();
            $expires = now()->addHour()->timestamp;

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->addHour(),
            ]);

            $signature = generateTestSignatureForCancel($token, $expires);

            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data',
                ])
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'Email change request cancelled successfully.',
                ]);

            // Token should be deleted
            $tokenRecord = EmailChangeTokenModel::where('user_id', $user->id)->first();
            expect($tokenRecord)->toBeNull();
        });

        it('only cancels pending requests not confirmed ones', function () {
            $user = UserModel::factory()->create();

            // Create a confirmed token (should not be deleted)
            $confirmedToken = EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'confirmed@example.com',
                'token' => Str::random(64),
                'expires_at' => now()->addHour(),
                'confirmed_at' => now(),
            ]);

            // Create a pending token
            $pendingToken = EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'pending@example.com',
                'token' => $token = generateTestTokenForCancel(),
                'expires_at' => now()->addHour(),
            ]);

            $expires = now()->addHour()->timestamp;
            $signature = generateTestSignatureForCancel($token, $expires);

            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(200);

            // Confirmed token should still exist
            $confirmedExists = EmailChangeTokenModel::where('id', $confirmedToken->id)->exists();
            expect($confirmedExists)->toBeTrue();

            // Pending token should be deleted
            $pendingExists = EmailChangeTokenModel::where('id', $pendingToken->id)->exists();
            expect($pendingExists)->toBeFalse();
        });
    });

    describe('HTTP request validation', function () {
        it('validates token is required', function () {
            $response = $this->postJson('/api/auth/email-change/cancel', [
                'expires' => now()->addHour()->timestamp,
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors',
                ])
                ->assertJsonPath('errors.token', fn ($errors) => in_array('The token field is required.', $errors));
        });

        it('validates expires is required', function () {
            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => str_repeat('a', 64),
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.expires', fn ($errors) => in_array('The expires field is required.', $errors));
        });

        it('validates signature is required', function () {
            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => str_repeat('a', 64),
                'expires' => now()->addHour()->timestamp,
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.signature', fn ($errors) => in_array('The signature field is required.', $errors));
        });

        it('validates token length', function () {
            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => 'short-token',
                'expires' => now()->addHour()->timestamp,
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.token', fn ($errors) => in_array('The token field must be 64 characters.', $errors));
        });
    });

    describe('Business rules', function () {
        it('returns 400 for invalid token', function () {
            $token = str_repeat('x', 64);
            $expires = now()->addHour()->timestamp;
            $signature = generateTestSignatureForCancel($token, $expires);

            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired cancellation link.',
                ]);
        });

        it('returns 400 for expired token', function () {
            $user = UserModel::factory()->create();

            $token = generateTestTokenForCancel();
            $expires = now()->subHour()->timestamp; // Expired

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->subHour(),
            ]);

            $signature = generateTestSignatureForCancel($token, $expires);

            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired cancellation link.',
                ]);
        });

        it('returns 400 for invalid signature', function () {
            $user = UserModel::factory()->create();

            $token = generateTestTokenForCancel();
            $expires = now()->addHour()->timestamp;

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->addHour(),
            ]);

            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => $token,
                'expires' => $expires,
                'signature' => 'invalid-signature',
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired cancellation link.',
                ]);
        });

        it('returns 400 for already confirmed token', function () {
            $user = UserModel::factory()->create();

            $token = generateTestTokenForCancel();
            $expires = now()->addHour()->timestamp;

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->addHour(),
                'confirmed_at' => now()->subMinute(), // Already confirmed
            ]);

            $signature = generateTestSignatureForCancel($token, $expires);

            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired cancellation link.',
                ]);
        });
    });

    describe('No authentication required', function () {
        it('works without authentication', function () {
            $user = UserModel::factory()->create([
                'email' => 'old@example.com',
            ]);

            $token = generateTestTokenForCancel();
            $expires = now()->addHour()->timestamp;

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->addHour(),
            ]);

            $signature = generateTestSignatureForCancel($token, $expires);

            // No actingAs() - no authentication
            $response = $this->postJson('/api/auth/email-change/cancel', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'Email change request cancelled successfully.',
                ]);
        });
    });
});
