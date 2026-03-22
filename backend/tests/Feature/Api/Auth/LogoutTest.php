<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

describe('POST /api/auth/logout', function () {
    describe('Happy path', function () {
        it('logs out authenticated user successfully', function () {
            $user = UserModel::factory()->create([
                'email' => 'john@example.com',
                'username' => 'johndoe',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->postJson('/api/auth/logout', [], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                ])
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'Logout successful.',
                ]);

            // Verify token was revoked
            $user->refresh();
            expect($user->tokens()->count())->toBe(0);
        });
    });

    describe('Authentication', function () {
        it('returns 401 for unauthenticated requests', function () {
            $response = $this->postJson('/api/auth/logout');

            $response->assertStatus(401);
        });

        it('returns 401 for invalid token', function () {
            $response = $this->postJson('/api/auth/logout', [], [
                'Authorization' => 'Bearer invalid-token',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Token revocation', function () {
        it('only revokes current token, not all tokens', function () {
            $user = UserModel::factory()->create([
                'email' => 'john@example.com',
            ]);

            // Create multiple tokens
            $token1 = $user->createToken('device-1')->plainTextToken;
            $user->createToken('device-2')->plainTextToken;

            // Use token1 for the request
            $response = $this->postJson('/api/auth/logout', [], [
                'Authorization' => 'Bearer '.$token1,
            ]);

            $response->assertStatus(200);

            // Verify only the current token was revoked
            $user->refresh();
            expect($user->tokens()->count())->toBe(1);
        });

        it('allows logout to be called for different sessions independently', function () {
            $user = UserModel::factory()->create();
            $token1 = $user->createToken('device-1')->plainTextToken;
            $token2 = $user->createToken('device-2')->plainTextToken;

            // Logout first session
            $response1 = $this->postJson('/api/auth/logout', [], [
                'Authorization' => 'Bearer '.$token1,
            ]);
            $response1->assertStatus(200);

            // Verify first token was revoked
            expect(\Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(1);

            // Clear authentication state for second request
            auth()->forgetUser();

            // Logout second session
            $response2 = $this->postJson('/api/auth/logout', [], [
                'Authorization' => 'Bearer '.$token2,
            ]);
            $response2->assertStatus(200);

            // Verify all tokens were revoked
            expect(\Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(0);
        });
    });
});
