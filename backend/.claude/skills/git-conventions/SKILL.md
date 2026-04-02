---
name: git-conventions
description: Branch naming, commit messages, and PR guidelines for this project
---

# Git Conventions

**IMPORTANT:** All GitHub interactions must use the `gh` CLI:
- Push branches: `git push -u origin <branch>` then `gh pr create`
- View PRs: `gh pr view`
- Merge PRs: `gh pr merge`

## Branch Naming

```
<layer>/<type>/<description>
```

| Part | Values |
|------|--------|
| **layer** | `backend`, `frontend`, `shared` |
| **type** | `feat`, `fix`, `chore`, `ref`, `tests`, `docs` |
| **description** | kebab-case description |

### Examples

```
backend/feat/change-password-handler
frontend/fix/login-redirect
backend/chore/update-env-example
```

## Commit Messages

```
<type>(<scope>): <description>
```

| Type | Purpose |
|------|---------|
| `feat` | New functionality |
| `fix` | Bug fix |
| `ref` | Code refactored (same behavior) |
| `chore` | Maintenance, config |
| `tests` | Only test files |
| `docs` | Only documentation |

### Examples

```
feat(backend): add ChangePasswordHandler
fix(frontend): resolve login redirect loop
tests(backend): add edge cases for password validation
```

## Pull Requests

The PR template is located at `.github/PULL_REQUEST_TEMPLATE.md` (relative to repo root). Use it as a guideline - not all sections are mandatory.