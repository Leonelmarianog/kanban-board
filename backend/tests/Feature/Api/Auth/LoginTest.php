<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

describe('POST /api/auth/login', function () {
    describe('Happy path', function () {
        it('logs in user with email', function () {
            $user = UserModel::factory()->create([
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => bcrypt('password123'),
            ]);

            $response = $this->postJson('/api/auth/login', [
                'email_or_username' => 'john@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(200)
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
                    'status' => 200,
                    'message' => 'Login successful.',
                ]);

            expect($response->json('data.0.token'))->toBeString();
        });

        it('logs in user with username', function () {
            UserModel::factory()->create([
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => bcrypt('password123'),
            ]);

            $response = $this->postJson('/api/auth/login', [
                'email_or_username' => 'johndoe',
                'password' => 'password123',
            ]);

            $response->assertStatus(200)
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
                    'status' => 200,
                    'message' => 'Login successful.',
                ]);

            expect($response->json('data.0.token'))->toBeString();
        });
    });

    describe('HTTP request validation', function () {
        it('validates required fields', function () {
            $response = $this->postJson('/api/auth/login', []);

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

        it('validates email_or_username is required', function () {
            $response = $this->postJson('/api/auth/login', [
                'password' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.email_or_username', fn ($errors) => in_array('The email or username field is required.', $errors));
        });

        it('validates password is required', function () {
            $response = $this->postJson('/api/auth/login', [
                'email_or_username' => 'john@example.com',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.password', fn ($errors) => in_array('The password field is required.', $errors));
        });
    });

    describe('Business rules', function () {
        it('returns 401 for invalid credentials with non-existent user', function () {
            $response = $this->postJson('/api/auth/login', [
                'email_or_username' => 'nonexistent@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(401)
                ->assertJsonFragment([
                    'status' => 401,
                    'message' => 'Invalid credentials.',
                ]);
        });

        it('returns 401 for invalid password', function () {
            UserModel::factory()->create([
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => bcrypt('password123'),
            ]);

            $response = $this->postJson('/api/auth/login', [
                'email_or_username' => 'john@example.com',
                'password' => 'wrongpassword',
            ]);

            $response->assertStatus(401)
                ->assertJsonFragment([
                    'status' => 401,
                    'message' => 'Invalid credentials.',
                ]);
        });

        it('returns 401 for invalid username with wrong password', function () {
            UserModel::factory()->create([
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => bcrypt('password123'),
            ]);

            $response = $this->postJson('/api/auth/login', [
                'email_or_username' => 'johndoe',
                'password' => 'wrongpassword',
            ]);

            $response->assertStatus(401)
                ->assertJsonFragment([
                    'status' => 401,
                    'message' => 'Invalid credentials.',
                ]);
        });

        it('returns 403 for unverified email', function () {
            UserModel::factory()->unverified()->create([
                'email' => 'unverified@example.com',
                'username' => 'unverifieduser',
                'password' => bcrypt('password123'),
            ]);

            $response = $this->postJson('/api/auth/login', [
                'email_or_username' => 'unverified@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(403)
                ->assertJsonFragment([
                    'status' => 403,
                    'message' => 'Please verify your email address before logging in.',
                ]);
        });
    });
});
