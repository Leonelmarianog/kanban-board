<!--
================================================================================
AI-MANAGED FILE - DO NOT EDIT MANUALLY
================================================================================
This file is automatically synchronized across multiple locations.
To update the workflow, ask Claude to update it - it will update all copies.
Locations:
  - backend/.ai/workflows/development-workflow.md
  - frontend/.ai/workflows/development-workflow.md
================================================================================
-->

# Development Workflow

This workflow applies to all development in this monorepo (backend and frontend).

## Workflow Summary

```
1. FEATURE BREAKDOWN
   → Split into units (200-400 lines each)
   → User approves

2. TEST PLANNING
   → Provide test case list
   → User approves or requests changes

3. IMPLEMENTATION (per unit)
   → Write code + tests together (tests included with code)
   → Create commits using pre-approved patterns
   → Report summary when complete

4. PR VERIFICATION
   → Propose PR title + description
   → User approves
   → Create PR on GitHub

5. CI/CD
   → Automated tests, linting, static analysis
   → If CI fails: notify user → user approves fix → push commits
   → User merges after CI passes

6. NEXT UNIT
   → Repeat from step 2
```

## Key Decisions

| Aspect | Decision |
|--------|----------|
| **PR Size** | 200-400 lines, 1-2 hours per PR |
| **Tests** | Included with code, not separate commits |
| **Break-fix** | No break-fix loop (trust well-written tests) |
| **Commits** | Use pre-approved patterns (no commit-by-commit approval) |
| **PR Creation** | Propose title/description → user approves → create PR |
| **CI Failures** | Notify user → user approves fix → push commits |
| **Merging** | User merges after CI passes |

## Branch Naming

```
<layer>/<type>/<description>

Layer: backend, frontend, shared
Type: feat, fix, chore, ref, tests, docs

Examples:
- backend/feat/change-password-handler
- frontend/fix/login-redirect
- backend/chore/update-env-example
```

## Commit Messages

```
<type>(<scope>): <description>

Types:
- feat: New functionality
- fix: Bug fixed
- ref: Code reorganized (same behavior)
- chore: Maintenance, config
- tests: Only test files
- docs: Only documentation
- style: Only formatting

Examples:
- feat(backend): add ChangePasswordHandler
- tests(backend): add edge case for password rate limiting
- fix(frontend): resolve login redirect issue
```

## Pre-Approved Commit Patterns

These commit patterns are pre-approved (no separate approval needed):

```
feat(backend): add {FeatureName}Handler
feat(backend): add {FeatureName}Controller and validation
feat(frontend): add {ComponentName} component
tests(backend): add edge case for {FeatureName}  # Only if missed test cases
```

## CI/CD Checklist

When adding new environment variables or configuration:
- [ ] Update `.env.example`
- [ ] Update GitHub workflow files (`.github/workflows/*.yml`)
- [ ] Note in PR description: "⚠️ New env vars: `VAR_NAME`"

## Workflow Files to Monitor

```
.github/workflows/*.yml
```

When adding new env vars or config, check these files and update if needed for CI/CD to pass.