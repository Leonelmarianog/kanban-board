<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

describe('PUT /api/v1/members/me/profile', function () {
    describe('Happy path', function () {
        it('updates the authenticated member profile', function () {
            $user = UserModel::factory()->create([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'bio' => 'Original bio',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'username' => 'janesmith',
                'bio' => 'Updated bio',
            ], [
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
                    'message' => 'Profile updated successfully.',
                ])
                ->assertJsonPath('data.0.full_name', 'Jane Smith')
                ->assertJsonPath('data.0.email', 'john@example.com')
                ->assertJsonPath('data.0.username', 'janesmith')
                ->assertJsonPath('data.0.bio', 'Updated bio');

            // Verify database was updated
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'username' => 'janesmith',
                'bio' => 'Updated bio',
            ]);
        });

        it('updates only bio keeping other fields same', function () {
            $user = UserModel::factory()->create([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'bio' => 'Original bio',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'bio' => 'New bio',
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.0.full_name', 'John Doe')
                ->assertJsonPath('data.0.email', 'john@example.com')
                ->assertJsonPath('data.0.username', 'johndoe')
                ->assertJsonPath('data.0.bio', 'New bio');
        });

        it('updates profile with null bio', function () {
            $user = UserModel::factory()->create([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'bio' => 'Original bio',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'bio' => null,
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.0.bio', null);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'bio' => null,
            ]);
        });
    });

    describe('Validation errors', function () {
        it('validates required fields', function () {
            $user = UserModel::factory()->create();
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['first_name', 'last_name', 'username']);
        });

        it('validates first_name min length', function () {
            $user = UserModel::factory()->create();
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => '',
                'last_name' => 'Doe',
                'username' => 'johndoe',
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['first_name']);
        });

        it('validates first_name max length', function () {
            $user = UserModel::factory()->create();
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => str_repeat('a', 256),
                'last_name' => 'Doe',
                'username' => 'johndoe',
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['first_name']);
        });

        it('validates last_name min length', function () {
            $user = UserModel::factory()->create();
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => '',
                'username' => 'johndoe',
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['last_name']);
        });

        it('validates username format', function () {
            $user = UserModel::factory()->create();
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'invalid-username!',
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['username']);
        });

        it('validates username min length', function () {
            $user = UserModel::factory()->create();
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'ab',
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['username']);
        });

        it('validates bio max length', function () {
            $user = UserModel::factory()->create();
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'bio' => str_repeat('a', 1001),
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['bio']);
        });
    });

    describe('Business rules', function () {
        it('prevents duplicate username', function () {
            $existingUser = UserModel::factory()->create(['username' => 'existinguser']);
            $user = UserModel::factory()->create(['username' => 'myusername']);

            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'existinguser',
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(422)
                ->assertJsonFragment([
                    'status' => 422,
                    'message' => "Username 'existinguser' is already taken.",
                ]);

            // Verify user's username was not changed
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'username' => 'myusername',
            ]);
        });

        it('allows keeping same username', function () {
            $user = UserModel::factory()->create([
                'username' => 'myusername',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'myusername',
                'bio' => 'New bio',
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.0.username', 'myusername')
                ->assertJsonPath('data.0.bio', 'New bio');
        });
    });

    describe('Authentication', function () {
        it('returns 401 for unauthenticated requests', function () {
            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
            ]);

            $response->assertStatus(401)
                ->assertJsonFragment([
                    'status' => 401,
                    'message' => 'Unauthenticated.',
                ]);
        });

        it('returns 401 for invalid token', function () {
            $response = $this->putJson('/api/v1/members/me/profile', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
            ], [
                'Authorization' => 'Bearer invalid-token',
            ]);

            $response->assertStatus(401);
        });
    });
});
