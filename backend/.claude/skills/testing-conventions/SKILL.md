---
name: testing-conventions
description: Pest PHP testing patterns for this Laravel project - test organization, assertions, factory usage
---

# Testing Conventions

This project uses **Pest PHP** for testing. Tests are located in `tests/Feature/` and `tests/Unit/`.

## Test Organization

| Location | Purpose |
|----------|---------|
| `tests/Feature/Api/` | API endpoint tests (controllers, routes) |
| `tests/Unit/` | Unit tests for domain entities, value objects |
| `tests/Feature/UseCases/` | Use case handler tests |

## Running Tests

```bash
# Run all tests
php artisan test --compact

# Run specific file
php artisan test --compact --filter=RegisterUserTest

# Run with coverage
php artisan test --coverage
```

## Test Structure

| Rule | Example |
|------|---------|
| Use `test()` with readable descriptions | `test('user can register with valid data', function () {` |
| Use `uses(RefreshDatabase::class)` for database tests | `uses(RefreshDatabase::class);` |
| Use factories for test data | `UserModel::factory()->create([...])` |
| Group related assertions in a single test | One test per scenario, not one assertion per test |

## Feature Test Example

```php
uses(RefreshDatabase::class);

test('user can register with valid data', function () {
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
        ->assertJsonStructure(['status', 'message'])
        ->assertJsonFragment([
            'status' => 201,
            'message' => 'Registration successful.',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);
});

test('registration fails with duplicate email', function () {
    UserModel::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/auth/register', [
        'email' => 'john@example.com',
        // ... other fields
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('message', "Email 'john@example.com' is already registered.");
});
```

## Unit Test Example (Domain Entity)

```php
test('user can be created with valid data', function () {
    $email = Email::fromString('john@example.com');
    $user = User::create(
        id: 'uuid-here',
        email: $email,
    );

    expect($user->id())->toBe('uuid-here')
        ->and($user->email()->value())->toBe('john@example.com');
});

test('email validation fails for invalid format', function () {
    Email::fromString('invalid-email');
})->throws(InvalidEmailException::class);
```

## Test Data

| Rule | Example |
|------|---------|
| Use `fake()` helper for Faker | `fake()->email()` |
| Use static cached values for passwords | Factory handles this |
| Create state methods for common variants | `$user = UserModel::factory()->unverified()->create()` |

## Assertions

| Type | Example |
|------|---------|
| HTTP status | `->assertStatus(201)` |
| JSON structure | `->assertJsonStructure(['status', 'message', 'data'])` |
| JSON fragment | `->assertJsonFragment(['status' => 201])` |
| JSON path | `->assertJsonPath('message', 'Success.')` |
| Database | `$this->assertDatabaseHas('users', ['email' => $email])` |
| Exception | `})->throws(InvalidEmailException::class);` |

## Test Naming

| Pattern | Example |
|---------|---------|
| Feature test file | `tests/Feature/Api/Auth/RegisterUserTest.php` |
| Unit test file | `tests/Unit/Domain/EmailTest.php` |
| Test description | `test('user can register with valid data', function () {` |
| Negative test | `test('registration fails with duplicate email', function () {` |

## Pest Features Used

| Feature | Usage |
|---------|-------|
| `uses()` | Import traits like `RefreshDatabase` |
| `test()` | Define tests with readable descriptions |
| `expect()` | Fluent assertions |
| `->throws()` | Expect exceptions |
| `->todo()` | Mark test as todo |

## What NOT to Test

| Skip | Reason |
|------|--------|
| Framework code | Laravel is already tested |
| Third-party packages | Their tests cover them |
| Trivial getters/setters | No logic to test |
| Configuration | Test behavior, not config |