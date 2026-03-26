<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

function generateTestToken(): string
{
    return Str::random(64);
}

function generateTestSignature(string $token, int $expires): string
{
    $frontendUrl = config('app.frontend_url').'/verify-email';
    $urlToSign = $frontendUrl.'?token='.$token;

    // Spatie URL signer uses this format: hash_hmac('sha256', "{$url}::{$expiration}", $key)
    return hash_hmac('sha256', "{$urlToSign}::{$expires}", config('app.key'));
}

describe('POST /api/auth/email-verification/verify', function () {
    describe('Happy path', function () {
        it('verifies email successfully', function () {
            $user = UserModel::factory()->unverified()->create([
                'email' => 'user@example.com',
            ]);

            $token = generateTestToken();
            $expires = now()->addMinutes(15)->timestamp;

            EmailVerificationTokenModel::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addMinutes(15),
            ]);

            $signature = generateTestSignature($token, $expires);

            $response = $this->postJson('/api/auth/email-verification/verify', [
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
                    'message' => 'Email verified successfully.',
                ]);

            // User should be verified
            $user->refresh();
            expect($user->email_verified_at)->not->toBeNull();

            // Token should be marked as used
            $tokenRecord = EmailVerificationTokenModel::where('user_id', $user->id)->first();
            expect($tokenRecord->used_at)->not->toBeNull();
        });

        it('returns success for already verified email', function () {
            $user = UserModel::factory()->create([
                'email' => 'verified@example.com',
                'email_verified_at' => now(),
            ]);

            $token = generateTestToken();
            $expires = now()->addMinutes(15)->timestamp;

            EmailVerificationTokenModel::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addMinutes(15),
            ]);

            $signature = generateTestSignature($token, $expires);

            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'Email already verified.',
                ]);
        });
    });

    describe('HTTP request validation', function () {
        it('validates token is required', function () {
            $response = $this->postJson('/api/auth/email-verification/verify', [
                'expires' => now()->addMinutes(15)->timestamp,
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
            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => str_repeat('a', 64),
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.expires', fn ($errors) => in_array('The expires field is required.', $errors));
        });

        it('validates signature is required', function () {
            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => str_repeat('a', 64),
                'expires' => now()->addMinutes(15)->timestamp,
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.signature', fn ($errors) => in_array('The signature field is required.', $errors));
        });

        it('validates token length', function () {
            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => 'short-token',
                'expires' => now()->addMinutes(15)->timestamp,
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.token', fn ($errors) => in_array('The token field must be 64 characters.', $errors));
        });

        it('validates expires is integer', function () {
            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => str_repeat('a', 64),
                'expires' => 'not-an-integer',
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.expires', fn ($errors) => in_array('The expires field must be an integer.', $errors));
        });
    });

    describe('Business rules', function () {
        it('returns 400 for invalid token', function () {
            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => str_repeat('x', 64),
                'expires' => now()->addMinutes(15)->timestamp,
                'signature' => 'some-signature',
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired verification link.',
                ]);
        });

        it('returns 400 for expired token', function () {
            $user = UserModel::factory()->unverified()->create();

            $token = generateTestToken();
            $expires = now()->subMinutes(5)->timestamp; // Expired

            EmailVerificationTokenModel::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->subMinutes(5),
            ]);

            $signature = generateTestSignature($token, $expires);

            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired verification link.',
                ]);
        });

        it('returns 400 for invalid signature', function () {
            $user = UserModel::factory()->unverified()->create();

            $token = generateTestToken();
            $expires = now()->addMinutes(15)->timestamp;

            EmailVerificationTokenModel::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addMinutes(15),
            ]);

            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => $token,
                'expires' => $expires,
                'signature' => 'invalid-signature',
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired verification link.',
                ]);
        });

        it('returns 400 for already used token', function () {
            $user = UserModel::factory()->unverified()->create();

            $token = generateTestToken();
            $expires = now()->addMinutes(15)->timestamp;

            EmailVerificationTokenModel::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addMinutes(15),
                'used_at' => now()->subMinute(), // Already used
            ]);

            $signature = generateTestSignature($token, $expires);

            $response = $this->postJson('/api/auth/email-verification/verify', [
                'token' => $token,
                'expires' => $expires,
                'signature' => $signature,
            ]);

            $response->assertStatus(400)
                ->assertJsonFragment([
                    'status' => 400,
                    'message' => 'Invalid or expired verification link.',
                ]);
        });
    });
});
