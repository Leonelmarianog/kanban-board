---
name: architecture-conventions
description: Clean Architecture patterns for this Laravel project - use-case isolation, repository per operation, domain purity, DTOs
---

# Architecture Conventions

## Core Principles

| Principle | Description |
|-----------|-------------|
| **Use-Case Isolation** | Each business operation has its own folder with dedicated files |
| **Repository Per Operation** | 1 use case = 1 repository. No generic/fat repositories. |
| **Domain Purity** | Domain entities have zero framework dependencies |
| **Single Responsibility** | Each use case does exactly one thing |

## Folder Structure

```
modules/{Module}/
├── Application/
│   └── UseCases/{Action}{Entity}/
│       ├── {Action}{Entity}Handler.php
│       ├── {Action}{Entity}RequestDto.php
│       ├── {Action}{Entity}ResponseDto.php
│       ├── {Action}{Entity}RepositoryInterface.php
│       ├── Exceptions/
│       └── README.md
├── Domain/
│   ├── Entities/{Entity}.php
│   └── ValueObjects/{ValueObject}.php
└── Infrastructure/
    └── Persistence/Repositories/{Action}{Entity}Repository.php
```

## Strict Constraints (Do NOT Do These)

| Constraint | Reason |
|------------|--------|
| **No Generic/Fat Repositories** | Every database operation gets its own dedicated repository. No `UserRepository` with CRUD methods. Use `CreateUserRepository`, `GetUserRepository`. |
| **No Dependencies in Domain** | Domain entities are pure PHP. No framework, no external libraries, no decorators, no facades. |
| **No Framework Bleed** | No Laravel decorators, attributes, or ORM annotations in Domain entities. |
| **No Repositories in Domain Layer** | Repositories belong in Infrastructure. Their interfaces in Application layer. |
| **No Magic Strings** | All meaningful string literals (statuses, types, categories) MUST be declared as constants/enums. |
| **No Env Var Fallbacks** | NEVER use `env('X') ?? 'default'` or `config('x') ?? 'default'`. Let required values fail loudly. |
| **No Cross-Handler Calls** | Use cases must NOT call other use cases. Extract shared logic to domain entities or services. |
| **No Comments** | Prefer self-explanatory code. Every comment represents a failure to make code clear. |
| **No barrel export files** | No `index.ts`/`index.php` files that export other files' exports. Import directly. |

## Use-Case Pattern

### Handler

```php
final readonly class RegisterUserHandler
{
    public function __construct(
        private RegisterUserRepositoryInterface $repository,
        private TransactionInterface $transaction,
    ) {}

    public function execute(RegisterUserRequestDto $request): RegisterUserResponseDto
    {
        return $this->transaction->execute(function () use ($request) {
            // Business logic here
            $user = User::create(...);
            return $this->repository->create($user);
        });
    }
}
```

### Repository Interface (in use-case folder)

```php
interface RegisterUserRepositoryInterface
{
    public function create(User $user): User;
    public function existsByEmail(string $email): bool;
}
```

### Repository Implementation (in Infrastructure)

```php
final readonly class RegisterUserRepository implements RegisterUserRepositoryInterface
{
    public function create(User $user): User
    {
        $model = new UserModel;
        $model->id = $user->id();
        $model->email = $user->email()->value();
        $model->save();

        return UserMapper::toDomain($model->fresh());
    }
}
```

## Domain Entities

```php
final class User
{
    private function __construct(
        private readonly string $id,
        private Email $email,
    ) {}

    public static function create(string $id, Email $email): self
    {
        return new self($id, $email);
    }

    public function id(): string { return $this->id; }
    public function email(): Email { return $this->email; }
}
```

- **Private constructor** - use factory methods (`create()`)
- **Getters for properties** - no public properties
- **Behavior through methods** - `$user->activate()` not `$user->status = 'active'`
- **No framework dependencies** - pure PHP only

## Value Objects

```php
final readonly class Email
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
        }
        return new self($value);
    }

    public function value(): string { return $this->value; }
}
```

## DTOs

```php
// Request DTO
final readonly class RegisterUserRequestDto
{
    public function __construct(
        public string $email,
        public string $username,
        public string $password,
    ) {}
}

// Response DTO
final readonly class RegisterUserResponseDto
{
    public function __construct(
        public string $id,
        public string $email,
    ) {}
}
```

- **Public properties only** - no methods
- **`final readonly class`** - immutable data containers

## Transactions

All database operations MUST be wrapped in transactions:

```php
return $this->transaction->execute(function () use ($request) {
    // All DB operations here
});
```

## Documentation

Each use case folder contains a `README.md` with:
- Use case ID and description
- Actors and stakeholders
- Preconditions and postconditions
- Main flow and alternate flows
- Business rules
- Sequence diagrams

## When to Read Full Docs

- **Read `docs/use-case-architecture.md`** when implementing a new use case or making structural changes
- **Read `docs/code-style.md`** when unsure about specific code patterns
- **Skip for** minor bug fixes, typos, or localized refactoring within a single file