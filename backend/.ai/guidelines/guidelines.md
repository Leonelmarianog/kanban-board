# Agent Instructions

You are an expert Laravel developer. Your goal is to write robust, maintainable code using Clean Architecture and modern PHP practices.

## Core Directives

* **Clean Code:** Write code that is easy to read, test, and maintain. Follow SOLID principles. Use descriptive names and keep functions focused on a single responsibility.
* **Clean Architecture:** Maintain strict separation of concerns. Domain entities have zero framework dependencies. Application layer defines interfaces. Infrastructure implements them.
* **Laravel Best Practices:** Use Eloquent relationships with return type hints. Prefer `Model::query()` over `DB::`. Use Form Requests for validation. Use `casts()` method, not `$casts` property.
* **Always Verify:** Before completing a task, review your changes. Check for missing dependencies, potential edge cases, and that tests pass.
* **Use-Case Specific Repositories:** One operation = one repository. `CreateUserRepository`, not `UserRepository`. Each use case owns its repository interface.
* **No Magic Strings:** All meaningful string literals (statuses, types, categories) MUST be declared as constants or enums. DO NOT use hardcoded strings like `'active'`, `'pending'`, `'admin'` anywhere.

## Strict Constraints (Do NOT Do These)

| Constraint | Reason |
|------------|--------|
| **No Generic/Fat Repositories** | Every database operation gets its own dedicated repository. No `UserRepository` with CRUD methods. |
| **No Dependencies in Domain** | Domain entities are pure PHP. No framework, no external libraries. Only business logic. |
| **No Framework Bleed** | No Laravel decorators, facades, or ORM annotations in Domain entities. |
| **No Repositories in Domain Layer** | Repositories belong in Infrastructure. Their interfaces in Application layer. |
| **No Env Var Fallbacks** | NEVER use `env('X') ?? 'default'` or `config('x') ?? 'default'`. Let required values fail loudly. |
| **No Cross-Handler Calls** | Use cases must NOT call other use cases. Extract shared logic to domain entities or services. |
| **No Comments** | Prefer self-explanatory code. Every comment represents a failure to make code clear. Use comments only as a last resort. |

## Code Style Quick Reference

| Category | Rule |
|----------|------|
| Controllers | Invokable, `final class`, return `JsonResponse` |
| Models | UUID primary keys, soft deletes, `casts()` method |
| Handlers | `final readonly class`, single responsibility |
| DTOs | `final readonly class`, public properties only |
| Domain Entities | `final class`, private constructor, factory methods, getters |
| Value Objects | `final readonly class`, static factory, validation in factory |

## Development Workflow

* **PR Size:** 200-400 lines, 1-2 hours per PR
* **Tests:** Included with code, not separate commits
* **Branch Naming:** `<layer>/<type>/<description>` (e.g., `backend/feat/change-password-handler`)
* **Commit Messages:** `<type>(<scope>): <description>` (e.g., `feat(backend): add ChangePasswordHandler`)

## Documentation & Deep Context

Deep context files are lazy-loaded when needed:

### Architecture (`docs/`)
- `docs/use-case-architecture.md` — Full architecture guide with examples
- `docs/code-style.md` — Detailed code style with examples

**When to read:** Implementing a new use case, making structural changes, or unsure about implementation patterns.
**When to skip:** Minor bug fixes, typos, or localized refactoring within a single file.

### Workflow (`.ai/workflows/`)
- `.ai/workflows/development-workflow.md` — Full development workflow (feature breakdown, test planning, PR process)

**When to read:** Starting a new feature, creating PRs, or unsure about commit/branch conventions.
**When to skip:** Minor changes that follow established patterns.

### Plans (`.ai/plans/`)
- `.ai/plans/module-organization-plan.md` — High-level module organization

**When to read:** Adding a new module or planning feature locations.
**When to skip:** Working within existing modules.
