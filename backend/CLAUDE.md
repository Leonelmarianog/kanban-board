<laravel-boost-guidelines>
=== .ai/guidelines rules ===

# Project-Specific Guidelines

These guidelines document the architecture, patterns, and conventions specific to this backend codebase. They complement the framework-level guidelines provided by Laravel Boost.

---

## Architecture Overview

This backend follows a **Modular Monolith (Clean Architecture / Hexagonal Architecture)** pattern:

- **Domain Layer**: Pure PHP entities with no framework dependencies, interfaces (Ports), domain exceptions
- **Application Layer**: Use cases (Handlers), DTOs, Commands, Queries (CQRS pattern)
- **Infrastructure Layer**: Framework-specific implementations (Eloquent models, controllers, adapters)

```
modules/
├── Auth/                          # Auth module

│   ├── Application/               # Application layer (CQRS)

│   │   ├── Commands/             # Command objects (write operations)

│   │   ├── DTOs/                 # Data Transfer Objects

│   │   ├── Handlers/             # Command/Query handlers

│   │   └── Queries/              # Query objects (read operations)

│   ├── Domain/                    # Domain layer (pure PHP)

│   │   ├── Entities/             # Domain entities

│   │   ├── Exceptions/           # Domain-specific exceptions

│   │   └── Ports/                # Interfaces/Contracts

│   ├── Infrastructure/            # Infrastructure layer (Laravel)

│   │   ├── Adapters/              # Port implementations

│   │   ├── Http/
│   │   │   ├── Controllers/       # HTTP controllers

│   │   │   ├── Requests/          # Form requests

│   │   │   └── Resources/         # API resources

│   │   ├── Mappers/              # Eloquent → Domain mappers

│   │   ├── Models/                # Eloquent models

│   │   └── Providers/             # Service providers

│   └── config.php                 # Module-specific config

└── Core/                          # Shared/core module

    ├── Application/Exceptions/    # ApplicationException base

    ├── Domain/Exceptions/          # BaseException, DomainException

    └── Infrastructure/
        ├── Exceptions/             # InfrastructureException

        └── Http/
            ├── Controllers/BaseController.php
            ├── Errors/ApiExceptionHandler.php
            └── Traits/ApiResponses.php
```

---

## Controllers

### Structure

- Controllers extend `BaseController` from `Core/Infrastructure/Http/Controllers/`
- Use `ApiResponses` trait for standardized responses
- Place in `modules/{Module}/Infrastructure/Http/Controllers/`

### Conventions

```php
final readonly class AuthController extends BaseController
{
    public function __construct(
        private readonly RegisterUserHandler $registerUserHandler,
        private readonly LoginUserHandler $loginUserHandler,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $command = new RegisterUserCommand(...);
        $authenticatedUser = $this->registerUserHandler->handle($command);

        return $this->success(
            message: 'Registration successful',
            statusCode: 201,
            data: ['token' => $authenticatedUser->token]
        );
    }
}
```

- Use `final readonly class` for controllers
- Constructor property promotion with `private readonly` for dependencies
- Controllers delegate to **Handler classes** (CQRS) - no business logic in controllers
- Methods return `JsonResponse` with explicit return types
- Use `$this->success()` and `$this->error()` from `ApiResponses` trait

---

## Models (Eloquent)

### Structure

- Models are in `modules/{Module}/Infrastructure/Models/`
- They are **infrastructure persistence models**, not domain entities
- Domain entities are in `modules/{Module}/Domain/Entities/`

### Conventions

```php
/**
 * @use HasFactory<UserFactory>
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'password',
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

    public function member(): HasOne
    {
        return $this->hasOne(MemberModel::class);
    }
}
```

- UUID primary keys (use `HasUuids` trait)
- Soft deletes (use `SoftDeletes` trait)
- Use `casts()` method, not `$casts` property (Laravel 11/12 convention)
- Add `@var list<string>` PHPDoc for `$fillable`
- Add `@use HasFactory<FactoryClass>` PHPDoc annotation
- Implement `newFactory()` for factory resolution
- Relationships use Eloquent return type hints (`HasOne`, `HasMany`, etc.)

---

## Repositories (Port-Adapter Pattern)

### Port (Interface) - Domain Layer

```php
// modules/{Module}/Domain/Ports/UserRepositoryInterface.php
interface UserRepositoryInterface
{
    public function findByIdOrFail(string $id): User;
    public function findByEmailOrFail(string $email): User;
    public function store(User $user): User;
    public function update(User $user): User;
}
```

### Adapter (Implementation) - Infrastructure Layer

```php
// modules/{Module}/Infrastructure/Adapters/EloquentUserRepository.php
final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByIdOrFail(string $id): User
    {
        $userModel = UserModel::findOrFail($id);
        return UserMapper::toDomain($userModel);
    }
}
```

### Conventions

- **Ports** return Domain entities, not Eloquent models
- **Adapters** use Mappers to convert Eloquent models to Domain entities
- Use `final class` for implementations
- Bind ports to adapters in ServiceProviders

---

## Mappers

Mappers convert Eloquent models to Domain entities:

```php
// modules/{Module}/Infrastructure/Mappers/UserMapper.php
final class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return new User(
            id: $model->id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
        );
    }

    public static function toModel(User $user): UserModel
    {
        $model = new UserModel;
        $model->id = $user->id;
        $model->first_name = $user->firstName;
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

- Place in `modules/{Module}/Infrastructure/Http/Requests/`

### Conventions

```php
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
```

- Use array-based validation rules (not pipe `|` syntax)
- `authorize()` returns `true` by default
- Add PHPDoc return type hint for rules

---

## CQRS (Commands, Queries, Handlers)

### Commands (Write Operations)

```php
// modules/{Module}/Application/Commands/RegisterUserCommand.php
final readonly class RegisterUserCommand
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
    ) {}
}
```

### Queries (Read Operations)

```php
// modules/{Module}/Application/Queries/GetCurrentMemberQuery.php
final readonly class GetCurrentMemberQuery
{
    public function __construct(
        public string $userId,
    ) {}
}
```

### Handlers

```php
// modules/{Module}/Application/Handlers/RegisterUserHandler.php
final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MemberRepositoryInterface $memberRepository,
        private PasswordHasherInterface $passwordHasher,
        private TransactionInterface $transaction,
    ) {}

    public function handle(RegisterUserCommand $command): AuthenticatedUserDTO
    {
        return $this->transaction->execute(function () use ($command) {
            // Business logic
        });
    }
}
```

### Conventions

- Use `final readonly class` for Commands, Queries, and Handlers
- Commands/Queries contain only public properties (no methods)
- Handlers receive Commands/Queries and return DTOs or Entities
- Wrap write operations in transactions via `TransactionInterface`

---

## DTOs (Data Transfer Objects)

```php
// modules/{Module}/Application/DTOs/MemberDto.php
final readonly class MemberDto
{
    public function __construct(
        public string $id,
        public string $fullName,
        public string $email,
        public ?string $avatarUrl,
        public ?string $bio,
    ) {}
}
```

- Use `final readonly class`
- Only public properties, no methods
- Used for data transfer between layers

---

## API Resources

### Structure

- Place in `modules/{Module}/Infrastructure/Http/Resources/`

### Conventions

```php
/**
 * @property-read MemberDto $resource
 */
class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'full_name' => $this->resource->fullName,
            'email' => $this->resource->email,
            'avatar_url' => $this->resource->avatarUrl,
            'bio' => $this->resource->bio,
        ];
    }
}
```

- Transform DTOs to JSON responses
- Add `@property-read` PHPDoc for type hints
- Use snake_case for JSON keys

---

## API Response Format

All API responses use a standardized format via `ApiResponses` trait:

### Success Response

```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful.",
    "data": { ... }
}
```

### Error Response

```json
{
    "success": false,
    "status": 400,
    "message": "Error message",
    "error": {
        "type": "ExceptionType",
        "message": "Detailed error",
        "code": 500,
        "timestamp": "2026-03-16T...",
        "validation_errors": { ... }
    },
    "debug": { ... }
}
```

- `debug` only included when `APP_DEBUG=true`
- `validation_errors` only for `ValidationException`

---

## Routes

### Structure

- `routes/api.php` - Base auth routes (`/api/auth/*`)
- `routes/api_v1.php` - Versioned API routes (`/api/v1/*`)

### Conventions

```php
// routes/api.php
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// routes/api_v1.php
Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/members/me', [MemberController::class, 'me']);
    });
});
```

- Use route groups with prefixes
- Apply middleware inline with `middleware('auth:sanctum')`
- Use array syntax for controllers: `[Controller::class, 'method']`

---

## Exception Hierarchy

```
BaseException (abstract)
├── DomainException (abstract)
│   ├── UserNotFoundException
│   ├── UserAlreadyExistsException
│   └── AuthenticationFailedException
├── ApplicationException (abstract)
│   └── MemberNotFoundException
└── InfrastructureException (abstract)
```

### Convention

```php
final class AuthenticationFailedException extends DomainException
{
    public function __construct(
        string $message = 'Authentication failed.',
        int $statusCode = 401
    ) {
        parent::__construct($message, $statusCode);
    }
}
```

- Domain exceptions extend `DomainException`
- Use `final class`
- Default message and status code in constructor

---

## Service Providers

Modules register their bindings in their own ServiceProvider:

```php
// modules/{Module}/Infrastructure/Providers/AuthServiceProvider.php
class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Port → Adapter bindings
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(MemberRepositoryInterface::class, EloquentMemberRepository::class);

        // Handler bindings with constructor injection
        $this->app->bind(RegisterUserHandler::class, function ($app) {
            return new RegisterUserHandler(
                $app->make(UserRepositoryInterface::class),
                $app->make(MemberRepositoryInterface::class),
                // ...other dependencies
            );
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config.php', 'auth');
    }
}
```

- Register in `bootstrap/providers.php`

---

## Database

### Migrations

```php
Schema::create('members', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('email')->unique();
    $table->timestamps();
    $table->softDeletes();
});
```

- UUID primary keys (`$table->uuid('id')->primary()`)
- Foreign keys with `constrained()` and `cascadeOnDelete()`
- Soft deletes on all tables
- Unique constraints where appropriate

### Factories

```php
class UserFactory extends Factory
{
    protected $model = UserModel::class;
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

- Use static password caching for performance
- Provide state methods for common variants

---

## Testing (Pest)

### Structure

Tests mirror module structure:

```
tests/
├── Feature/Auth/Infrastructure/
│   ├── Adapters/
│   └── Http/
└── Unit/Auth/
    ├── Application/DTOs/
    ├── Domain/Entities/
    └── Infrastructure/
        ├── Adapters/
        └── Mappers/
```

### Conventions

```php
uses(RefreshDatabase::class);

describe('POST /api/auth/register', function () {
    describe('Happy path', function () {
        it('registers a new user', function () {
            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(201)
                ->assertJsonStructure(['success', 'status', 'message', 'data'])
                ->assertJsonFragment(['success' => true]);
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
- Assert JSON structure and fragments

---

## Summary Table

| Aspect | Pattern |
|--------|---------|
| **Architecture** | Modular Monolith with Clean/Hexagonal Architecture |
| **CQRS** | Commands (write) / Queries (read) with Handlers |
| **Dependency Injection** | ServiceProvider bindings, constructor injection |
| **Domain Entities** | Pure PHP classes, no framework dependencies |
| **Repositories** | Port (Interface) in Domain, Adapter in Infrastructure |
| **Mappers** | Static methods convert Eloquent ↔ Domain entities |
| **DTOs** | `final readonly class` for data transfer |
| **API Resources** | Transform DTOs to JSON |
| **Validation** | Form Request classes |
| **Authentication** | Laravel Sanctum |
| **Primary Keys** | UUIDs |
| **Soft Deletes** | Enabled on all models |
| **Testing** | Pest PHP, Feature/Unit separation |
| **API Responses** | Standardized JSON via `ApiResponses` trait |

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5.1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`, `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

</laravel-boost-guidelines>
