# Contributing

## Development Workflow

> **Note:** The full development workflow is defined in `.ai/workflows/development-workflow.md` (AI-managed file).
> To update the workflow, ask Claude to update it - it will update all copies.

### Workflow Summary

1. **Feature Breakdown** → Split into units (200-400 lines) → User approves
2. **Test Planning** → Provide test case list → User approves/requests changes
3. **Implementation** → Write code + tests together → Create commits → Report summary
4. **PR Verification** → Propose PR title/description → User approves → Create PR
5. **CI/CD** → If fails: notify user → user approves fix → push commits → User merges

## Git Conventions

### Branch Names

Format: `<layer>/<type>/<description>`

- **layer**: `backend`, `frontend`, `shared`
- **type**: `feat`, `fix`, `chore`, `ref`, `tests`, `docs`
- **description**: kebab-case description

Examples:
- `backend/feat/change-password-handler`
- `frontend/fix/login-redirect`
- `backend/chore/update-env-example`

### Commit Messages

Format: `<type>(<scope>): <description>`

Follow [Conventional Commits](https://www.conventionalcommits.org/).

| Type | When to use | Example |
|------|-------------|---------|
| `feat` | New functionality | `feat(backend): add change password endpoint` |
| `fix` | Bug fixed | `fix(backend): resolve login token issue` |
| `ref` | Code reorganized, same behavior | `ref(backend): split UserHandler` |
| `chore` | Maintenance, config | `chore(backend): update .env.example` |
| `tests` | Only test files | `tests(backend): add email validation tests` |
| `docs` | Only documentation | `docs: update contributing guide` |
| `style` | Only formatting | `style(backend): fix pint issues` |

### Pull Requests

- Target **200-400 lines** per PR
- Complete within **1-2 hours**
- Use PR template
- Link related issues

### PR Workflow

Before creating a PR on GitHub:
1. Implementation is reviewed and approved
2. PR title and description are proposed for review
3. PR is created after user approval