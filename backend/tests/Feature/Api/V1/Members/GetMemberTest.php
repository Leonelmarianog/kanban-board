<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

describe('GET /api/v1/members/me', function () {
    describe('Happy path', function () {
        it('returns the authenticated member profile', function () {
            $user = UserModel::factory()->create([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'picture' => 'https://example.com/avatar.jpg',
                'bio' => 'Software developer',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->getJson('/api/v1/members/me', [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        [
                            'id',
                            'full_name',
                            'email',
                            'username',
                            'avatar_url',
                            'bio',
                        ],
                    ],
                ])
                ->assertJsonFragment([
                    'status' => 200,
                    'message' => 'Member profile retrieved successfully.',
                ])
                ->assertJsonPath('data.0.full_name', 'John Doe')
                ->assertJsonPath('data.0.email', 'john@example.com')
                ->assertJsonPath('data.0.username', 'johndoe')
                ->assertJsonPath('data.0.avatar_url', 'https://example.com/avatar.jpg')
                ->assertJsonPath('data.0.bio', 'Software developer');
        });

        it('returns member profile with null optional fields', function () {
            $user = UserModel::factory()->create([
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@example.com',
                'username' => 'janesmith',
                'picture' => null,
                'bio' => null,
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->getJson('/api/v1/members/me', [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.0.full_name', 'Jane Smith')
                ->assertJsonPath('data.0.avatar_url', null)
                ->assertJsonPath('data.0.bio', null);
        });
    });

    describe('Authentication', function () {
        it('returns 401 for unauthenticated requests', function () {
            $response = $this->getJson('/api/v1/members/me');

            $response->assertStatus(401)
                ->assertJsonFragment([
                    'status' => 401,
                    'message' => 'Unauthenticated.',
                ]);
        });

        it('returns 401 for invalid token', function () {
            $response = $this->getJson('/api/v1/members/me', [
                'Authorization' => 'Bearer invalid-token',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Edge cases', function () {
        it('returns 401 when user is deleted (token becomes invalid)', function () {
            $user = UserModel::factory()->create();
            $token = $user->createToken('auth-token')->plainTextToken;

            // Delete the user - Sanctum token becomes invalid
            $user->forceDelete();

            $response = $this->getJson('/api/v1/members/me', [
                'Authorization' => 'Bearer '.$token,
            ]);

            // Sanctum returns 401 because the token's user no longer exists
            $response->assertStatus(401);
        });
    });
});
