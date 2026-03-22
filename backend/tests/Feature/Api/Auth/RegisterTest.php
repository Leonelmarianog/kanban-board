<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

describe('POST /api/auth/register', function () {
    describe('Happy path', function () {
        it('registers a new user', function () {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'username' => 'johndoe',
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        [
                            'token',
                        ],
                    ],
                ])
                ->assertJsonFragment([
                    'status' => 201,
                    'message' => 'Registration successful.',
                ]);

            expect($response->json('data.0.token'))->toBeString();

            $this->assertDatabaseHas('users', [
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]);
        });
    });

    describe('HTTP request validation', function () {
        it('validates required fields', function () {
            $userData = [];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors',
                ])
                ->assertJsonFragment([
                    'status' => 422,
                    'message' => 'One or more validation errors occurred.',
                ]);
        });

        it('validates email format', function () {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'invalid-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'username' => 'johndoe',
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonPath('errors.email', fn ($errors) => in_array('The email field must be a valid email address.', $errors));
        });

        it('validates password confirmation', function () {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different_password',
                'username' => 'johndoe',
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonPath('errors.password', fn ($errors) => in_array('The password field confirmation does not match.', $errors));
        });

        it('validates password minimum length', function () {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'pass',
                'password_confirmation' => 'pass',
                'username' => 'johndoe',
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonPath('errors.password', fn ($errors) => in_array('The password field must be at least 8 characters.', $errors));
        });

        it('validates username format', function () {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'username' => 'john@doe',
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonPath('errors.username', fn ($errors) => in_array('The username field format is invalid.', $errors));
        });

        it('validates username minimum length', function () {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'username' => 'jo',
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonPath('errors.username', fn ($errors) => in_array('The username field must be at least 3 characters.', $errors));
        });
    });

    describe('Business rules', function () {
        it('prevents registering with an existing email', function () {
            UserModel::factory()->create([
                'email' => 'john@example.com',
                'username' => 'existinguser',
            ]);

            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'username' => 'newuser',
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(409)
                ->assertJsonFragment([
                    'status' => 409,
                ])
                ->assertJsonPath('message', "Email 'john@example.com' is already registered.");
        });

        it('prevents registering with an existing username', function () {
            UserModel::factory()->create([
                'email' => 'other@example.com',
                'username' => 'johndoe',
            ]);

            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'username' => 'johndoe',
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(409)
                ->assertJsonFragment([
                    'status' => 409,
                ])
                ->assertJsonPath('message', "Username 'johndoe' is already taken.");
        });
    });
});
