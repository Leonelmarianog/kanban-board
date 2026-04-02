---
name: laravel-conventions
description: Laravel-specific patterns for controllers, models, form requests, migrations, routes, and API responses
---

# Laravel Conventions

## Controllers

| Rule | Example |
|------|---------|
| **Invokable (single-action)** | `Route::post('auth/register', RegisterUserController::class)` |
| Use `final class` (no `readonly` - controllers have no properties) | `final class RegisterUserController extends BaseController` |
| Constructor property promotion with `private readonly` | `public function __construct(private readonly RegisterUserHandler $handler) {}` |
| Methods return `JsonResponse` with explicit return types | `public function __invoke(RegisterRequest $request): JsonResponse` |
| Use `ApiResponses` trait for responses | `$this->success(...)`, `$this->error(...)` |

```php
final class RegisterUserController extends BaseController
{
    use ApiResponses;

    public function __construct(private readonly RegisterUserHandler $handler) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $dto = new RegisterUserRequestDto(
            email: $request->validated('email'),
            username: $request->validated('username'),
            password: $request->validated('password'),
        );

        $response = $this->handler->execute($dto);

        return $this->success('Registration successful.', 201, [
            'token' => $response->token,
        ]);
    }
}
```

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

```php
/**
 * @use HasFactory<UserFactory>
 * @property string $id
 * @property string $email
 * @property string $username
 * @var list<string> $fillable
 */
final class User extends Authenticatable
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'email',
        'username',
        'password',
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
        return $this->hasOne(Member::class);
    }
}
```

## Form Requests

| Rule | Example |
|------|---------|
| Use array-based validation rules (not pipe `\|` syntax) | `'email' => ['required', 'string', 'email']` |
| `authorize()` returns `true` by default | `public function authorize(): bool { return true; }` |
| Add PHPDoc return type hint for rules | `/** @return array<string, ValidationRule\|array<mixed>\|string> */` |

```php
final class RegisterRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
```

## Migrations

| Rule | Example |
|------|---------|
| UUID primary keys | `$table->uuid('id')->primary()` |
| Foreign keys with `constrained()` and `cascadeOnDelete()` | `$table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete()` |
| Soft deletes on all tables | `$table->softDeletes()` |
| Unique constraints where appropriate | `$table->string('email')->unique()` |

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
```

## Routes

| Rule | Example |
|------|---------|
| Use route groups with prefixes | `Route::prefix('auth')->group(...)` |
| Apply middleware inline | `Route::middleware('auth:sanctum')->group(...)` |
| Use array syntax for controllers | `[Controller::class, 'method']` |
| Invokable syntax for single-action | `Route::post('auth/register', RegisterUserController::class)` |

```php
Route::prefix('auth')->group(function () {
    Route::post('register', RegisterUserController::class);
    Route::post('login', LoginUserController::class);
    Route::post('logout', LogoutUserController::class)->middleware('auth:sanctum');
});
```

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

## Factories

| Rule | Example |
|------|---------|
| Use static password caching | `protected static ?string $password;` |
| Provide state methods for common variants | `public function unverified(): static` |

```php
final class UserFactory extends Factory
{
    protected static ?string $password;

    protected $model = User::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= 'password',
            'email_verified_at' => now(),
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

## Exceptions

| Rule | Example |
|------|---------|
| Domain exceptions have **no HTTP awareness** (no status codes) | `DomainException extends BaseException` |
| Application exceptions: one base per use case | `RegisterUserException` (abstract) |
| Specific exceptions extend the use-case base | `EmailAlreadyExistsException extends RegisterUserException` |
| Controllers map exceptions to HTTP status codes | `catch (EmailAlreadyExistsException $e) { return $this->error(statusCode: 409, ...) }` |

## PHP Style

| Rule | Example |
|------|---------|
| Always use curly braces for control structures | `if ($x) { ... }` not `if ($x): ...` |
| Use constructor property promotion | `public function __construct(public GitHub $github) {}` |
| Always use explicit return type declarations | `protected function isAccessible(User $user): bool` |
| Use appropriate PHP type hints for parameters | `function process(string $name, ?int $count = null)` |
| Enums in TitleCase | `FavoritePerson`, `BestLake`, `Monthly` |
| Prefer PHPDoc blocks over inline comments | Only comment when logic is exceptionally complex |
| No empty `__construct()` with zero parameters | Unless constructor is private |