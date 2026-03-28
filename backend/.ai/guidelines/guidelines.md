# Project-Specific Guidelines

These guidelines document the architecture, patterns, and conventions specific to this backend codebase. They complement the framework-level guidelines provided by Laravel Boost.

---

## Architecture Overview

This backend follows a **Modular Monolith (Clean Architecture / Hexagonal Architecture)** pattern:

- **Domain Layer**: Pure PHP entities with no framework dependencies, interfaces (Ports), domain exceptions
- **Application Layer**: Use cases (Handlers with Request/Response DTOs)
- **Infrastructure Layer**: Framework-specific implementations (Eloquent models, controllers, adapters)

```
modules/
├── Application/
│   ├── Exceptions/
│   │   └── ApplicationException.php
│   └── UseCases/
│       └── Auth/
│           └── RegisterUser/
│               ├── Exceptions/
│               │   ├── RegisterUserException.php
│               │   ├── EmailAlreadyExistsException.php
│               │   └── UsernameAlreadyExistsException.php
│               ├── RegisterUserHandler.php
│               ├── RegisterUserRepositoryInterface.php
│               ├── RegisterUserRequestDto.php
│               └── RegisterUserResponseDto.php
├── Domain/
│   ├── Exceptions/
│   │   ├── DomainException.php
│   │   └── ValidationDomainException.php
│   ├── User/
│   │   └── User.php
│   └── ValueObjects/
│       ├── Email.php
│       ├── Username.php
│       ├── UserFullName.php
│       ├── HashedPassword.php
│       └── Exceptions/
│           ├── InvalidEmailException.php
│           ├── InvalidUsernameFormatException.php
│           ├── InvalidUsernameLengthException.php
│           └── InvalidFullNameException.php
└── Infrastructure/
    ├── Http/
    │   ├── Controllers/
    │   │   ├── BaseController.php
    │   │   └── Auth/
    │   │       └── RegisterUserController.php
    │   ├── Exceptions/
    │   │   └── ApiExceptionHandler.php
    │   ├── Requests/
    │   │   └── RegisterRequest.php
    │   └── Traits/
    │       └── ApiResponses.php
    ├── Persistence/
    │   ├── Models/
    │   │   └── UserModel.php
    │   ├── Mappers/
    │   │   └── UserMapper.php
    │   └── Repositories/
    │       └── RegisterUserRepository.php
    └── Providers/
        └── RepositoryServiceProvider.php
```

---

## Controllers

### Structure

- Controllers are **invokable (single-action)** - one controller per use case
- Organize by feature: `Controllers/Auth/RegisterUserController.php`
- Extend `BaseController` from `Infrastructure/Http/Controllers/`
- Use `ApiResponses` trait for standardized responses

### Conventions

```php
// One controller per use case (invokable)
final class RegisterUserController extends BaseController
{
    public function __construct(
        private RegisterUserHandler $handler,
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        try {
            $requestDto = new RegisterUserRequestDto(...);
            $response = $this->handler->execute($requestDto);

            return $this->success(
                message: 'Registration successful.',
                statusCode: 201,
                data: ['token' => $response->token],
            );
        } catch (ValidationDomainException $e) {
            return $this->error(statusCode: 422, message: $e->getMessage());
        } catch (EmailAlreadyExistsException|UsernameAlreadyExistsException $e) {
            return $this->error(statusCode: 409, message: $e->getMessage());
        } catch (RegisterUserException $e) {
            return $this->error(statusCode: 500, message: $e->getMessage());
        }
    }
}
```

- Use `final class` for controllers (no `readonly` - controllers have no properties)
- Constructor property promotion with `private readonly` for dependencies
- Controllers delegate to **Handler classes** - no business logic in controllers
- Methods return `JsonResponse` with explicit return types
- Controller catches use-case-specific exceptions and maps to HTTP status codes

### Route Definition

```php
// routes/api.php
use Modules\Infrastructure\Http\Controllers\Auth\RegisterUserController;

Route::post('auth/register', RegisterUserController::class);
```

- Use invokable syntax: `Route::post('path', ControllerClass::class)`
- No array syntax `[Controller::class, 'method']` for invokable controllers

---

## API Response Format

All API responses use a standardized format via `ApiResponses` trait:

### Success Response

```json
{
    "status": 201,
    "message": "Registration successful.",
    "data": [
        { "token": "..." }
    ]
}
```

- `data` is always an array
- Empty data returns `"data": []`

### Error Response

```json
{
    "status": 422,
    "message": "One or more validation errors occurred.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

- `errors` uses Laravel's validation format (field → array of messages)
- Only included when there are validation errors

### Business Rule Error

```json
{
    "status": 409,
    "message": "Email 'john@example.com' is already registered."
}
```

- No `errors` key for business rule violations

---

## Exception Hierarchy

### Domain Layer

```
Domain/Exceptions/
├── DomainException.php           # Base domain exception
└── ValidationDomainException.php # For value object validation errors

Domain/ValueObjects/Exceptions/
├── InvalidEmailException.php
├── InvalidUsernameFormatException.php
├── InvalidUsernameLengthException.php
└── InvalidFullNameException.php
```

- Domain exceptions have **no HTTP awareness** - no status codes
- Specific exceptions extend `ValidationDomainException`

### Application Layer

```
Application/UseCases/Auth/RegisterUser/Exceptions/
├── RegisterUserException.php         # Base exception (abstract)
├── EmailAlreadyExistsException.php   # Extends RegisterUserException
└── UsernameAlreadyExistsException.php # Extends RegisterUserException
```

- Each use case has one base exception
- Specific business rule exceptions extend it
- No HTTP status codes in exceptions - controllers determine status codes

### Exception Handling Flow

```
HTTP Request → Controller (catches, maps to HTTP) → Handler → Domain
```

1. **Controller** catches specific exceptions and returns appropriate HTTP responses
2. **Handler** throws business rule exceptions
3. **Domain** throws validation exceptions (safety net for invariants)

---

## Repository Error Handling Pattern

### Critical Rule: Repositories Do NOT Throw Exceptions

**Infrastructure layer (repositories, external services) must NEVER throw exceptions.** Instead, they return values that allow the Application layer to decide how to proceed.

### Why?

- **Application layer owns business logic decisions** - including error handling
- **Infrastructure is an implementation detail** - it should not dictate control flow
- **Testability** - easier to mock repositories that return values vs throw exceptions
- **Clean Architecture principle** - infrastructure should be a dumb data access layer

### Pattern: Return `null` for "Not Found"

```php
// ❌ WRONG - Repository throws exception
interface GetMemberRepositoryInterface
{
    public function findByIdOrFail(string $id): User;  // BAD
}

final class GetMemberRepository implements GetMemberRepositoryInterface
{
    public function findByIdOrFail(string $id): User
    {
        $model = UserModel::find($id);

        if ($model === null) {
            throw new MemberNotFoundException($id);  // WRONG - infra throwing!
        }

        return UserMapper::toDomain($model);
    }
}

// ✅ CORRECT - Repository returns null, Handler throws exception
interface GetMemberRepositoryInterface
{
    public function findById(string $id): ?User;  // Returns null if not found
}

final class GetMemberRepository implements GetMemberRepositoryInterface
{
    public function findById(string $id): ?User
    {
        $model = UserModel::find($id);

        if ($model === null) {
            return null;  // Return null, let Application layer decide
        }

        return UserMapper::toDomain($model);
    }
}

// Handler throws the exception
final readonly class GetMemberHandler
{
    public function execute(GetMemberRequestDto $request): GetMemberResponseDto
    {
        $user = $this->repository->findById($request->memberId);

        if ($user === null) {
            throw new MemberNotFoundException($request->memberId);  // Application layer throws
        }

        return $this->toResponseDto($user);
    }
}
```

### When to Use Each Approach

| Scenario | Repository Returns | Handler Throws |
|----------|---------------------|----------------|
| Entity not found | `null` | `EntityNotFoundException` |
| Validation needed | Entity (valid) | `ValidationException` if invalid |
| External service failure | `null` or error DTO | Handler decides: retry, fail, etc. |

### Method Naming Convention

| Method | Returns | Behavior |
|--------|---------|----------|
| `findById()` | `?User` | Returns `null` if not found |
| `findByEmail()` | `?User` | Returns `null` if not found |
| `emailExists()` | `bool` | Returns `true`/`false` |
| `save()` | `User` | Always returns the saved entity |
| `createToken()` | `?string` | Returns `null` if creation fails |

### Controller Layer Responsibility

Controllers catch exceptions thrown by Handlers and map them to HTTP responses:

```php
final class GetMemberController extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $response = $this->handler->execute($requestDto);
            return $this->success(...);
        } catch (MemberNotFoundException $e) {
            return $this->error(statusCode: 404, message: $e->getMessage());
        } catch (ApplicationException $e) {
            return $this->error(statusCode: 500, message: $e->getMessage());
        }
    }
}
```

---

## Models (Eloquent)

### Structure

- Models are in `modules/Infrastructure/Persistence/Models/`
- They are **infrastructure persistence models**, not domain entities
- Domain entities are in `modules/Domain/`

### Conventions

```php
/**
 * @use HasFactory<UserFactory>
 */
class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'password',
        'username',
        'picture',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
```

- UUID primary keys (use `HasUuids` trait)
- Soft deletes (use `SoftDeletes` trait)
- Use `casts()` method, not `$casts` property (Laravel 11/12 convention)
- Add `@var list<string>` PHPDoc for `$fillable`
- Add `@use HasFactory<FactoryClass>` PHPDoc annotation

---

## Repositories (Port-Adapter Pattern)

### Port (Interface) - Application Layer

```php
// Application/UseCases/Auth/RegisterUser/RegisterUserRepositoryInterface.php
interface RegisterUserRepositoryInterface
{
    public function emailExists(string $email): bool;
    public function usernameExists(string $username): bool;
    public function save(User $user): User;
    public function createToken(User $user, string $tokenName): ?string;
}
```

### Adapter (Implementation) - Infrastructure Layer

```php
// Infrastructure/Persistence/Repositories/RegisterUserRepository.php
final readonly class RegisterUserRepository implements RegisterUserRepositoryInterface
{
    public function __construct(
        private UserModel $model,
    ) {}

    public function emailExists(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }
    // ...
}
```

### Conventions

- **Ports** return Domain entities, not Eloquent models
- **Adapters** use Mappers to convert Eloquent models to Domain entities
- Use `final readonly class` for implementations
- Bind ports to adapters in ServiceProviders

---

## Mappers

Mappers convert Eloquent models to Domain entities:

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
            password: HashedPassword::fromHash($model->password),
            username: $model->username,
            picture: $model->picture,
            bio: $model->bio,
            createdAt: new DateTimeImmutable($model->created_at->toIso8601String()),
            updatedAt: new DateTimeImmutable($model->updated_at->toIso8601String()),
            deletedAt: $model->deleted_at ? new DateTimeImmutable($model->deleted_at->toIso8601String()) : null,
        );
    }

    public static function toModel(User $user): UserModel
    {
        $model = new UserModel;
        $model->id = $user->getId();
        $model->first_name = $user->getFirstName()->getValue();
        // ...
        return $model;
    }
}
```

- Use `final class` with static methods
- `toDomain()` converts Model → Entity
- `toModel()` converts Entity → Model (when needed)

---

## Form Requests

### Structure

- Place in `modules/Infrastructure/Http/Requests/`

### Conventions

```php
final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:1', 'max:255'],
            'last_name' => ['required', 'string', 'min:1', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/'],
        ];
    }
}
```

- Use array-based validation rules (not pipe `|` syntax)
- `authorize()` returns `true` by default
- Add PHPDoc return type hint for rules

---

## Handlers (Use Cases)

### Structure

```php
final readonly class RegisterUserHandler
{
    public function __construct(
        private RegisterUserRepositoryInterface $repository,
    ) {}

    public function execute(RegisterUserRequestDto $request): RegisterUserResponseDto
    {
        // Business logic...
    }
}
```

### Conventions

- Use `final readonly class` for Handlers
- Handlers receive DTOs and return DTOs
- Throw business rule exceptions (e.g., `EmailAlreadyExistsException`)
- Domain validation exceptions bubble up from Value Objects

---

## DTOs (Data Transfer Objects)

```php
final readonly class RegisterUserRequestDto
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public string $username,
    ) {}
}
```

- Use `final readonly class`
- Only public properties, no methods
- Used for data transfer between layers

---

## Testing (Pest)

### Structure

```
tests/
├── Feature/
│   └── Api/
│       └── Auth/
│           └── RegisterTest.php
└── Unit/
    ├── Domain/
    │   ├── User/
    │   │   └── UserTest.php
    │   └── ValueObjects/
    │       ├── EmailTest.php
    │       ├── UsernameTest.php
    │       ├── UserFullNameTest.php
    │       └── HashedPasswordTest.php
    └── Infrastructure/
        ├── Http/
        │   ├── Traits/
        │   │   └── ApiResponsesTest.php
        │   └── Exceptions/
        │       └── ApiExceptionHandlerTest.php
        └── Persistence/
            └── Mappers/
                └── UserMapperTest.php
```

### Conventions

```php
describe('POST /api/auth/register', function () {
    describe('Happy path', function () {
        it('registers a new user', function () {
            // ...
        });
    });

    describe('HTTP request validation', function () {
        it('validates required fields', function () {
            // ...
        });
    });

    describe('Business rules', function () {
        it('prevents duplicate email registration', function () {
            // ...
        });
    });
});
```

- Use `describe()` blocks for grouping
- Organize by: `Happy path`, `HTTP request validation`, `Business rules`
- Use `RefreshDatabase` trait for database tests
- Use factories for test data: `UserModel::factory()->create()`

---

## Summary Table

| Aspect | Pattern |
|--------|---------|
| **Architecture** | Modular Monolith with Clean/Hexagonal Architecture |
| **Use Cases** | Handler + RequestDto + ResponseDto per use case |
| **Controllers** | Invokable (single-action), organized by feature |
| **Routes** | `Route::post('path', ControllerClass::class)` |
| **Dependency Injection** | ServiceProvider bindings, constructor injection |
| **Domain Entities** | Pure PHP classes, no framework dependencies |
| **Repositories** | Port (Interface) in Application, Adapter in Infrastructure |
| **Mappers** | Static methods convert Eloquent ↔ Domain entities |
| **DTOs** | `final readonly class` for data transfer |
| **Validation** | Form Request classes (primary), Domain Value Objects (safety net) |
| **Exception Handling** | Controller catches, maps to HTTP status codes |
| **Authentication** | Laravel Sanctum |
| **Primary Keys** | UUIDs |
| **Soft Deletes** | Enabled on all models |
| **Testing** | Pest PHP, Feature/Unit separation |
| **API Responses** | Standardized JSON via `ApiResponses` trait |