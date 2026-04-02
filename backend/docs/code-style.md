# Code Style Guidelines

This document outlines the coding style guidelines for the application.

---

## PHP

| Rule | Example |
|------|---------|
| Always use curly braces for control structures | `if ($x) { ... }` not `if ($x): ...` |
| Use constructor property promotion | `public function __construct(public GitHub $github) {}` |
| Always use explicit return type declarations | `protected function isAccessible(User $user): bool` |
| Use appropriate PHP type hints for parameters | `function process(string $name, ?int $count = null)` |
| Enums in TitleCase | `FavoritePerson`, `BestLake`, `Monthly` |
| Prefer PHPDoc blocks over inline comments | Only comment when logic is exceptionally complex |
| No empty `__construct()` with zero parameters | Unless constructor is private |

---

## Controllers

| Rule | Example |
|------|---------|
| **Invokable (single-action)** - one controller per use case | `Route::post('auth/register', RegisterUserController::class)` |
| Use `final class` (no `readonly` - controllers have no properties) | `final class RegisterUserController extends BaseController` |
| Constructor property promotion with `private readonly` | `public function __construct(private readonly RegisterUserHandler $handler) {}` |
| Methods return `JsonResponse` with explicit return types | `public function __invoke(RegisterRequest $request): JsonResponse` |
| Use `ApiResponses` trait for responses | `$this->success(...)`, `$this->error(...)` |

---

## Models (Eloquent)

| Rule | Example |
|------|---------|
| UUID primary keys (use `HasUuids` trait) | `$table->uuid('id')->primary()` |
| Soft deletes (use `SoftDeletes` trait) | `$table->softDeletes()` |
| Use `casts()` method, not `$casts` property | `protected function casts(): array { return [...]; }` |
| Add `@var list<string>` PHPDoc for `$fillable` | `/** @var list<string> */` |
| Add `@use HasFactory<FactoryClass>` PHPDoc | `/** @use HasFactory<UserFactory> */` |
| Implement `newFactory()` for factory resolution | `protected static function newFactory(): UserFactory` |
| Relationships use Eloquent return type hints | `public function member(): HasOne` |

---

## Form Requests

| Rule | Example |
|------|---------|
| Use array-based validation rules (not pipe `\|` syntax) | `'email' => ['required', 'string', 'email']` |
| `authorize()` returns `true` by default | `public function authorize(): bool { return true; }` |
| Add PHPDoc return type hint for rules | `/** @return array<string, ValidationRule\|array<mixed>\|string> */` |

---

## Handlers and DTOs

| Rule | Example |
|------|---------|
| Use `final readonly class` | `final readonly class RegisterUserHandler` |
| Constructor property promotion with `private readonly` | `public function __construct(private readonly RegisterUserRepositoryInterface $repository) {}` |
| DTOs have only public properties, no methods | `public string $id, public string $email` |

---

## Domain Entities

| Rule | Example |
|------|---------|
| Use `final class` | `final class User` |
| Private constructor | `private function __construct(...)` |
| Factory methods for creation | `public static function create(string $name): self` |
| Getters for properties | `public function id(): string { return $this->id; }` |
| Behavior through methods | `$contact->blacklist()` not `$contact->isBlacklisted = true` |
| **No dependencies** - no framework, no external libraries | Only pure PHP |

---

## Value Objects

| Rule | Example |
|------|---------|
| Use `final readonly class` | `final readonly class Email` |
| Use static factory methods | `public static function fromString(string $value): self` |
| Use getters for values | `public function value(): string { return $this->value; }` |
| Validate in factory method | Throw exception for invalid values |
| **No dependencies** | Only pure PHP |

```php
final readonly class Email
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
```

---

## Mappers

| Rule | Example |
|------|---------|
| **One mapper per entity** | `UserMapper`, `MemberMapper` |
| **Only `toDomain` method** | `UserMapper::toDomain(UserModel $model): User` |
| **No `toModel` method** | Repositories map domain to model internally |
| Use static methods | `public static function toDomain(UserModel $model): User` |
| Call entity's `create()` factory | `User::create(...)` - same method used everywhere |

```php
final class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return User::create(
            id: $model->id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            username: $model->username,
        );
    }
}
```

---

## Repositories (Persistence)

| Rule | Example |
|------|---------|
| Use `final readonly class` | `final readonly class RegisterUserRepository` |
| Interface in use-case folder | `UseCases/RegisterUser/RegisterUserRepositoryInterface.php` |
| Implementation in `Infrastructure/Persistence/Repositories/` | `Infrastructure/Persistence/Repositories/RegisterUserRepository.php` |
| Inject models via constructor | `public function __construct(private UserModel $model) {}` |
| **Create**: `new Model` + set properties + `save()` | Or use `$model->create([...])` for simple cases |
| **Update**: `findOrFail()` + `fill([...])` + `save()` | Enforces `$fillable` protection |
| **Read**: `find()` + return `Mapper::toDomain()` | Return `null` if not found |

```php
// Create - new model + set properties
public function save(User $user): User
{
    $model = new UserModel;
    $model->id = $user->getId();
    $model->first_name = $user->getFirstName()->getValue();
    $model->email = $user->getEmail()->getValue();
    $model->save();

    return UserMapper::toDomain($model->fresh());
}

// Create - with create() method
public function saveToken(EmailChangeToken $token): void
{
    $this->tokenModel->create([
        'id' => $token->getId(),
        'user_id' => $token->getUserId(),
        'token' => $token->getToken(),
    ]);
}

// Update - findOrFail + fill + save
public function update(User $user): User
{
    $model = $this->model->findOrFail($user->getId());

    $model->fill([
        'first_name' => $user->getFirstName()->getValue(),
        'username' => $user->getUsername()->getValue(),
    ]);

    $model->save();

    return UserMapper::toDomain($model);
}

// Read - find + toDomain
public function findById(string $id): ?User
{
    $model = $this->model->find($id);

    if ($model === null) {
        return null;
    }

    return UserMapper::toDomain($model);
}
```

---

## API Response Format

### Success Response

```json
{
    "status": 201,
    "message": "Registration successful.",
    "data": [{ "token": "..." }]
}
```

| Rule | Example |
|------|---------|
| `data` is always an array (empty `[]` if no data) | `$this->success('Success.', 200, ['key' => 'value'])` |
| `data` wraps the payload in an array | `'data' => [['token' => $token]]` |
| Use `$this->success()` from `ApiResponses` trait | |

### Error Response

```json
{
    "status": 422,
    "message": "One or more validation errors occurred.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

| Rule | Example |
|------|---------|
| `errors` only included when validation errors exist | `$this->error(422, 'Validation failed.', ['email' => ['Required']])` |
| Use Laravel's validation format (field → array of messages) | |
| Use `$this->error()` from `ApiResponses` trait | |

---

## Routes

| Rule | Example |
|------|---------|
| Use route groups with prefixes | `Route::prefix('auth')->group(...)` |
| Apply middleware inline | `Route::middleware('auth:sanctum')->group(...)` |
| Use array syntax for controllers | `[Controller::class, 'method']` |
| Invokable syntax for single-action | `Route::post('auth/register', RegisterUserController::class)` |

---

## Exceptions

| Rule | Example |
|------|---------|
| Domain exceptions have **no HTTP awareness** (no status codes) | `DomainException extends BaseException` |
| Application exceptions: one base per use case | `RegisterUserException` (abstract) |
| Specific exceptions extend the use-case base | `EmailAlreadyExistsException extends RegisterUserException` |
| Controllers map exceptions to HTTP status codes | `catch (EmailAlreadyExistsException $e) { return $this->error(statusCode: 409, ...) }` |

---

## Migrations

| Rule | Example |
|------|---------|
| UUID primary keys | `$table->uuid('id')->primary()` |
| Foreign keys with `constrained()` and `cascadeOnDelete()` | `$table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete()` |
| Soft deletes on all tables | `$table->softDeletes()` |
| Unique constraints where appropriate | `$table->string('email')->unique()` |

---

## Factories

| Rule | Example |
|------|---------|
| Use static password caching | `protected static ?string $password;` |
| Provide state methods for common variants | `public function unverified(): static` |

---

## Testing (Pest)

| Rule | Example |
|------|---------|
| Use `test()` with readable descriptions | `test('user can register with valid data', function () {` |
| Use `uses(RefreshDatabase::class)` for database tests | |
| Use factories for test data | `UserModel::factory()->create([...])` |
| Assert JSON structure and fragments | `->assertJsonStructure(['status', 'message', 'data'])` |
| Group related assertions in a single test | One test per scenario, not one assertion per test |

### Example

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
