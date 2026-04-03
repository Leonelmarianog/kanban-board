<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Infrastructure\Mail\Mailables\PasswordChangedMailable;
use Modules\Infrastructure\Persistence\Models\UserModel;

uses(RefreshDatabase::class);

test('user can change password with valid credentials', function () {
    Mail::fake();

    $user = UserModel::factory()->create([
        'password' => Hash::make('current-password'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->patchJson('/api/auth/change-password', [
            'current_password' => 'current-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Password changed successfully. You have been logged out from other devices.',
        ]);

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();

    Mail::assertQueued(PasswordChangedMailable::class, function ($mail) use ($user) {
        return $mail->user->getId() === $user->id;
    });
});

test('change password fails with wrong current password', function () {
    $user = UserModel::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->patchJson('/api/auth/change-password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'status' => 422,
            'message' => 'The current password is incorrect.',
        ]);
});

test('change password fails when new password is same as current', function () {
    $user = UserModel::factory()->create([
        'password' => Hash::make('same-password'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->patchJson('/api/auth/change-password', [
            'current_password' => 'same-password',
            'password' => 'same-password',
            'password_confirmation' => 'same-password',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'status' => 422,
            'message' => 'The new password must be different from your current password.',
        ]);
});

test('change password fails with password confirmation mismatch', function () {
    $user = UserModel::factory()->create([
        'password' => Hash::make('current-password'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->patchJson('/api/auth/change-password', [
            'current_password' => 'current-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'different-password',
        ]);

    $response->assertStatus(422);
});

test('change password fails with password shorter than 8 characters', function () {
    $user = UserModel::factory()->create([
        'password' => Hash::make('current-password'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->patchJson('/api/auth/change-password', [
            'current_password' => 'current-password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

    $response->assertStatus(422);
});

test('change password fails for unauthenticated requests', function () {
    $response = $this->patchJson('/api/auth/change-password', [
        'current_password' => 'current-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertStatus(401);
});

test('change password revokes other sessions but keeps current session', function () {
    $user = UserModel::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $token1 = $user->createToken('session-1')->plainTextToken;
    $user->createToken('session-2')->plainTextToken;
    $user->createToken('session-3')->plainTextToken;

    $response = $this->withToken($token1)
        ->patchJson('/api/auth/change-password', [
            'current_password' => 'current-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response->assertStatus(200);

    expect($user->tokens()->count())->toBe(1);

    $this->withToken($token1)
        ->getJson('/api/v1/members/me')
        ->assertStatus(200);
});
