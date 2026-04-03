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

## OpenApi Attributes (Swagger)

| Rule | Example |
|------|---------|
| Named arguments must match constructor parameter order | `new OA\Property(property: 'email', type: 'string', example: 'a@b.c')` |
| Multi-line attributes for readability | See example below |
| One attribute per line in multi-line | `new OA\Property(...)` on separate lines |

### Parameter Order by Attribute

| Attribute | Order |
|-----------|-------|
| `OA\Property` | property, type, format, example |
| `OA\Response` | response, description, content |
| `OA\RequestBody` | required, content |
| `OA\JsonContent` | required, properties |
| `OA\Patch`, `OA\Post`, etc. | path, description, summary, security, requestBody, tags, responses |

```php
#[OA\Patch(
    path: '/api/auth/password',
    description: 'Change the authenticated user\'s password.',
    summary: 'Change password',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['current_password', 'password'],
            properties: [
                new OA\Property(
                    property: 'current_password',
                    type: 'string',
                    example: 'oldpassword123'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    example: 'newpassword123'
                ),
            ]
        )
    ),
    tags: ['Password'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Password changed successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'integer',
                        example: 200
                    ),
                ]
            )
        ),
    ]
)]
```

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
| Use `use` statements, not inline FQCN in PHPDoc | `/** @var UserModel\|null $user */` not `/** @var \Modules\...\UserModel\|null $user */` |
| Import classes in `use` statements, not FQCN in PHPDoc | `use App\Models\User;` then `@var User` not `@var \App\Models\User` |

## Timestamps

| Rule | Example |
|------|---------|
| Let Eloquent handle `updated_at` automatically | Use `$model->fill([...])->save()` not `where()->update(['updated_at' => now()])` |
| Domain entities do NOT update timestamps | Entity methods only modify domain state, not `updatedAt` |
| Use `fill()->save()` pattern for updates | `$model->fill(['field' => $value])->save()` |
| Check `$fillable` before mass assignment | Attributes not in `$fillable` are guarded and cannot be set via `fill()` |

### Why Entity Methods Don't Update Timestamps

Domain entities should not be aware of persistence concerns. The `updated_at` column is a database/persistence detail, not a domain concept.

```php
// Wrong: Domain entity managing timestamps
public function changePassword(HashedPassword $newPassword): void
{
    $this->password = $newPassword;
    $this->updatedAt = new DateTimeImmutable;  // Don't do this
}

// Correct: Entity only manages domain state
public function changePassword(HashedPassword $newPassword): void
{
    $this->password = $newPassword;
}
```

```php
// Correct: Repository handles persistence details
public function updatePassword(User $user): void
{
    $model = $this->model->findOrFail($user->getId());
    $model->fill(['password' => $user->getPassword()->getHashedValue()]);
    $model->save();  // Eloquent automatically sets updated_at
}
```

## Type Hints for IDE Support

| Rule | Example |
|------|---------|
| Use PHPDoc `@var` for inferred types | `/** @var PersonalAccessToken $token */` |
| Assign to variable before accessing properties | Don't chain calls that return interfaces |
| Import classes for type hints | `use Laravel\Sanctum\PersonalAccessToken;` then `@var PersonalAccessToken` |

### Sanctum Token Type Hint

When accessing `currentAccessToken()` on an authenticated user, use a variable with PHPDoc:

```php
// Wrong: IDE can't infer the type, warns about 'id' property
$tokenId = (string) $user->currentAccessToken()->id;

// Correct: PHPDoc tells IDE the actual type
/** @var PersonalAccessToken $token */
$token = $user->currentAccessToken();
$tokenId = (string) $token->id;
```

### Why This Matters

- `currentAccessToken()` returns `HasAbilities|null` (an interface)
- `HasAbilities` doesn't have an `id` property
- `PersonalAccessToken` implements `HasAbilities` and has the `id` property
- PHPDoc `@var` tells the IDE the actual runtime type

## Guarded Attributes

| Rule | Example |
|------|---------|
| Always check `$fillable` before using `fill()` | Attributes not in `$fillable` are guarded |
| Use `fill()->save()` instead of `where()->update()` | `fill()` respects `$fillable`, `update()` bypasses it |
| Never manually set `updated_at` in queries | Eloquent handles timestamps automatically on `save()` |

### Checking Model Fillable

Before using `fill()`, verify the attribute is in the model's `$fillable` array:

```php
// UserModel $fillable includes: 'password'
// ✅ Correct: 'password' is fillable
$model->fill(['password' => $hashedPassword])->save();

// ❌ Wrong: 'updated_at' is NOT fillable (guarded)
$model->fill(['password' => $hashedPassword, 'updated_at' => now()])->save();
// This will silently ignore 'updated_at'!
```

### Why `where()->update()` Causes Issues

```php
// ❌ Problematic: Bypasses Eloquent's mass assignment protection
$this->model->where('id', $userId)->update([
    'password' => $hashedPassword,
    'updated_at' => now(),  // IDE warns: guarded attribute
]);

// ✅ Correct: Uses Eloquent properly
$model = $this->model->findOrFail($userId);
$model->fill(['password' => $hashedPassword]);
$model->save();  // Eloquent sets updated_at automatically
```