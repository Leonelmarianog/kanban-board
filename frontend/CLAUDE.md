# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Vue 3 + TypeScript frontend application for a Kanban Board. Uses Vite as the build tool, Pinia for state management, and TanStack Vue Query for server state.

## Common Commands

```bash
npm run dev              # Start development server (http://localhost:5173)
npm run build            # Production build (outputs to ./dist)
npm run preview          # Preview production build
npm run test:unit        # Run unit tests with Vitest
npm run test:unit -- src/components/__tests__/BoardCard.spec.ts  # Run single test file
npm run test:e2e         # Run end-to-end tests with Playwright
npm run coverage         # Run unit tests with coverage report
npm run typecheck        # TypeScript type checking
npm run lint             # Lint and auto-fix errors
npm run lint:check       # Check lint errors without fixing
npm run format           # Format code with Prettier
npm run format:check     # Check formatting without modifying
```

## Architecture

- **src/http/**: HTTP client abstraction layer with `FetchHttpClient` and `HttpError`
- **src/api/backend/**: Backend API client wrapper (`BackendClient`) with typed response interfaces and `BackendError`
- **src/services/backend/**: Backend API service functions (auth, etc.) with service-specific errors
- **src/stores/**: Pinia stores for client state (auth, card, list)
- **src/composables/**: Vue composables for reusable logic (e.g., useRegister, useToggle)
- **src/components/**: Vue components (UI components in root, feature components organized by feature)
- **src/views/**: Page-level components (HomeView, RegisterView)
- **src/forms/**: Form data classes with `toFormData()` method for API submissions
- **src/entities/**: Value objects and domain entities (e.g., AuthToken)
- **src/types/**: Shared TypeScript type definitions

### Key Patterns

#### HTTP Layer Abstraction

- `HttpClientInterface` defines the contract for HTTP clients
- `FetchHttpClient` implements the interface using the native Fetch API
- `HttpError` wraps HTTP error responses with status and data

#### Backend API Client

- `BackendClient` wraps `HttpClientInterface` and adds `Content-Type: application/json` header
- Converts `HttpError` to `BackendError` for domain-specific error handling
- API responses use `BackendSuccessResponseInterface<T>` with `success`, `message`, `status`, and `data`
- Error responses follow `BackendErrorResponseInterface` with structured error objects

#### Service Layer

- Services use `BackendClient` for API calls
- Service-specific errors (e.g., `AuthServiceError`) provide domain context
- `AuthServiceError.fromBackendError()` maps backend errors to service errors

#### Form Data

- Form classes extend `BaseFormData<T>` and implement `toFormData()` for multipart/form-data submissions
- `RegisterFormData` converts form fields to `FormData` for API requests

#### Auth Flow

- Auth token stored in localStorage and managed via Pinia auth store
- `useRegister` composable handles registration mutation with TanStack Vue Query
- On success, stores token and redirects to home

#### Testing Conventions

- Unit tests use Vitest with `@vue/test-utils` for Vue components
- Mock external dependencies (Pinia stores, router, composables) using `vi.mock()`
- Use `importOriginal` when partial mocking is needed (e.g., keeping real class exports)
- For complex library types, use `as unknown as ReturnType<typeof fn>` type assertions
- Use `computed()` from Vue when mocking composables that return `ComputedRef`

## Environment Variables

Create a `.env` file with:

```
VITE_API_BASE_URL=http://localhost:8080/api
```
