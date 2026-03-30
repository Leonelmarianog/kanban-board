<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Infrastructure\Persistence\Models\EmailChangeTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

function generateTestTokenForEmailChange(): string
{
    return Str::random(64);
}

function generateTestSignatureForEmailChange(string $token, int $expires): string
{
    $frontendUrl = config('app.frontend_url').'/email-change/confirm';
    $urlToSign = $frontendUrl.'?token='.$token;

    return hash_hmac('sha256', "{$urlToSign}::{$expires}", config('app.key'));
}

describe('POST /api/auth/email-change/confirm', function () {
    describe('Happy path', function () {
        it('confirms email change successfully', function () {
            $user = UserModel::factory()->create([
                'email' => 'old@example.com',
            ]);

            // Create a token for the user
            $user->createToken('test-token');

            $token = generateTestTokenForEmailChange();
            $expires = now()->addHour()->timestamp;

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->addHour(),
            ]);

            $signature = generateTestSignatureForEmailChange($token, $expires);

            $response = $this->postJson('/api/auth/email-change/confirm', [
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
                    'message' => 'Email changed successfully. Please log in with your new email.',
                ]);

            // User's email should be updated
            $user->refresh();
            expect($user->email)->toBe('new@example.com')
                ->and($user->email_verified_at)->not->toBeNull();

            // Token should be marked as confirmed
            $tokenRecord = EmailChangeTokenModel::where('user_id', $user->id)->first();
            expect($tokenRecord->confirmed_at)->not->toBeNull();

            // All tokens should be revoked
            $remainingTokens = PersonalAccessToken::where('tokenable_id', $user->id)->count();
            expect($remainingTokens)->toBe(0);
        });
    });

    describe('HTTP request validation', function () {
        it('validates token is required', function () {
            $response = $this->postJson('/api/auth/email-change/confirm', [
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
            $response = $this->postJson('/api/auth/email-change/confirm', [
                'token' => str_repeat('a', 64),
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.expires', fn ($errors) => in_array('The expires field is required.', $errors));
        });

        it('validates signature is required', function () {
            $response = $this->postJson('/api/auth/email-change/confirm', [
                'token' => str_repeat('a', 64),
                'expires' => now()->addHour()->timestamp,
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.signature', fn ($errors) => in_array('The signature field is required.', $errors));
        });

        it('validates token length', function () {
            $response = $this->postJson('/api/auth/email-change/confirm', [
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
            $response = $this->postJson('/api/auth/email-change/confirm', [
                'token' => str_repeat('x', 64),
                'expires' => now()->addHour()->timestamp,
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired email change link.',
                ]);
        });

        it('returns 400 for expired token', function () {
            $user = UserModel::factory()->create();

            $token = generateTestTokenForEmailChange();
            $expires = now()->subHour()->timestamp; // Expired

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->subHour(),
            ]);

            $signature = generateTestSignatureForEmailChange($token, $expires);

            $response = $this->postJson('/api/auth/email-change/confirm', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired email change link.',
                ]);
        });

        it('returns 400 for invalid signature', function () {
            $user = UserModel::factory()->create();

            $token = generateTestTokenForEmailChange();
            $expires = now()->addHour()->timestamp;

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->addHour(),
            ]);

            $response = $this->postJson('/api/auth/email-change/confirm', [
                'token' => $token,
                'expires' => $expires,
                'signature' => 'invalid-signature',
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired email change link.',
                ]);
        });

        it('returns 400 for already confirmed token', function () {
            $user = UserModel::factory()->create();

            $token = generateTestTokenForEmailChange();
            $expires = now()->addHour()->timestamp;

            EmailChangeTokenModel::create([
                'user_id' => $user->id,
                'current_email' => 'old@example.com',
                'new_email' => 'new@example.com',
                'token' => $token,
                'expires_at' => now()->addHour(),
                'confirmed_at' => now()->subMinute(), // Already confirmed
            ]);

            $signature = generateTestSignatureForEmailChange($token, $expires);

            $response = $this->postJson('/api/auth/email-change/confirm', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired email change link.',
                ]);
        });
    });
});
