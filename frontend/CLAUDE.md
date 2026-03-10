# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) for working with code in this repository.

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

```
src/
├── api/
│   ├── ApiError.ts              # Base error class for API errors
│   └── backend/
│       ├── client.ts            # Backend API client wrapper
│       ├── types.ts             # API response types
│       ├── auth/index.ts        # Auth API functions (login, logout, register)
│       └── member/index.ts      # Member API functions (getMe)
├── composables/
│   ├── mutations/               # Mutation hooks (useLogin, useLogout, useRegister)
│   ├── queries/                 # Query hooks and options (useMeQuery, memberOptions)
│   │   └── queryKeys.ts         # Query key factory pattern
│   └── shared/                  # Shared composables (useToggle, useValidationErrors)
├── components/                  # Vue components
├── views/                       # Page-level components
├── stores/                      # Pinia stores (auth)
├── forms/                       # Form data classes with toFormData()
├── router/                      # Vue Router configuration
├── types/                       # Shared TypeScript type definitions
└── __tests__/                   # Root-level tests (App.spec.ts)

test/
├── setup.ts                     # Vitest setup with MSW server
├── handlers.ts                  # MSW default handlers (happy path)
└── helpers.ts                   # Test utilities (mountWithPlugins, fillForm, submitForm)
```

### Key Patterns

#### API Layer

- `backendClient(path, options)` - Thin wrapper around fetch with `VITE_API_BASE_URL` prefix
- Returns `Promise<T>` on success, throws `ApiError` on failure
- All API functions return the data directly, not wrapped in a result object

```typescript
// Example usage - throws on error
const data = await backendClient<AuthToken>('/auth/login', { method: 'POST', body: formData });
// data is typed as AuthToken

// Error handling is done via try/catch or TanStack Query's onError
```

#### Error Handling

The app uses a three-tier error handling strategy for TanStack Query:

1. **Global QueryCache.onError** - Handles authentication errors (missing/invalid token)
2. **Local mutation.onError** - Handles form validation errors and specific error types
3. **Query isPending/error states** - For loading and error UI in components

**ApiError Class:**

```typescript
// src/api/ApiError.ts
export enum ApiErrorType {
  AuthenticationException = 'AuthenticationException', // Missing/invalid token
  AuthenticationFailedException = 'AuthenticationFailedException', // Wrong credentials
  ValidationException = 'ValidationException', // Form validation errors
}

export class ApiError extends Error {
  readonly type: ApiErrorType;
  readonly code: number;
  readonly status: number;
  readonly validationErrors?: Record<string, string[]>;

  static create(response: BackendErrorResponse): ApiError;
}
```

**Global Auth Error Handler (main.ts):**

```typescript
const queryClient = new QueryClient({
  queryCache: new QueryCache({
    onError: (error) => {
      if (error instanceof ApiError && error.type === ApiErrorType.AuthenticationException) {
        authStore.clearAuth();
        router.push({ name: 'Login' });
        toast.error('Your session has expired. Please log in again.');
      }
    },
  }),
});
```

**Local Error Handling in Mutations:**

```typescript
// In mutation composable - just return the API promise
export function useLogin() {
  return useMutation({
    mutationFn: (data: LoginFormData) => authApi.login(data.toFormData()),
  });
}

// In component - handle specific error types
login(formData, {
  onSuccess: (data) => {
    authStore.setAuth(data.token);
    router.push({ name: 'Home' });
  },
  onError: (error) => {
    if (error instanceof ApiError && error.type === ApiErrorType.ValidationException) {
      setErrors(error.validationErrors); // Show validation errors in form
    } else if (
      error instanceof ApiError &&
      error.type === ApiErrorType.AuthenticationFailedException
    ) {
      toast.error('Username or password incorrect.');
    } else {
      toast.error('An unexpected error occurred.');
    }
  },
});
```

#### Query Keys (TanStack Query)

Use factory objects for query keys to enable hierarchical cache operations:

```typescript
// src/composables/queries/queryKeys.ts
export const memberKeys = {
  all: ['member'] as const,
  me: () => [...memberKeys.all, 'me'] as const,
} as const;

// Usage
useQuery(memberOptions.me()); // queryKey: ['member', 'me']
queryClient.invalidateQueries({ queryKey: memberKeys.all }); // Invalidate all member queries
```

#### Query Options Pattern

Use `queryOptions` for reusable query configurations:

```typescript
// src/composables/queries/memberOptions.ts
export const memberOptions = {
  me: () =>
    queryOptions({
      queryKey: memberKeys.me(),
      queryFn: async () => {
        /* ... */
      },
      staleTime: 5 * 60 * 1000,
    }),
};

// Usage in component
const { data } = useQuery(memberOptions.me());
```

#### Mutations

- Logic callbacks (cache updates, invalidation) go in `useMutation` options
- UI callbacks (redirects, toasts, validation errors) go in `mutate` call's `onError`/`onSuccess` options
- Return simple wrapper around `useMutation`, letting the API layer throw errors

```typescript
// useMutation composable - minimal, just wraps API function
export function useLogout() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: () => authApi.logout(),
    onSuccess: () => {
      queryClient.removeQueries({ queryKey: memberKeys.all });
    },
  });
}

// In component - handle success and specific error types
logout(undefined, {
  onSuccess: () => {
    authStore.clearAuth();
    router.push({ name: 'Login' });
  },
  onError: (error) => {
    // Handle specific error types locally
    toast.error('Logout failed. Please try again.');
  },
});
```

#### Auth Flow

- Auth token stored in localStorage, managed by Pinia auth store
- `useMeQuery` fetches current user when authenticated
- `useLogin`/`useLogout`/`useRegister` handle auth mutations

## Testing Conventions

### MSW for API Mocking

Use Mock Service Worker (MSW) for all API mocking. Never mock fetch or API client functions directly.

```typescript
// test/handlers.ts - Default happy path handlers
export const handlers = [
  http.post('*/api/auth/login', () => {
    return HttpResponse.json({ success: true, data: { token: 'test-token' } });
  }),
];

// Override in tests for edge cases
server.use(
  http.post('*/api/auth/login', () => {
    return HttpResponse.json(
      { success: false, error: { message: 'Invalid credentials' } },
      { status: 401 },
    );
  }),
);
```

**Important:** Use wildcard origin (`*/api/...`) in handlers to match any `VITE_API_BASE_URL` from environment.

### Test Helpers

```typescript
// test/helpers.ts
mountWithPlugins(Component, { slots: { default: 'content' } }); // Mount with Pinia + Vue Query
await fillForm(wrapper, { email: 'test@example.com', password: 'pass' });
await submitForm(wrapper);
```

### Vue Query Setup

Each test gets a fresh `QueryClient` with retries disabled:

```typescript
// Built into mountWithPlugins
const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: false },
    mutations: { retry: false },
  },
});
```

### Vue Router Mocking

For unit tests, mock the router:

```typescript
const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({ push: mockPush }),
}));

// In tests
await vi.waitFor(() => {
  expect(mockPush).toHaveBeenCalledWith({ name: 'Login' });
});
```

### Test Structure

```typescript
describe('Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
    // Note: server.resetHandlers() is called automatically in global afterEach
  });

  it('should do something', async () => {
    const wrapper = mountWithPlugins(Component);
    // assertions...
  });
});
```

### Avoid Test Duplication

Test behavior where it's defined, not where it's consumed:

- **PageLayout.spec.ts** - Tests logout button, authentication state, logout flow
- **HomeView.spec.ts** - Tests only HomeView-specific content (heading, child components)

Don't test the same behavior in multiple places. If a component delegates to another component, test the delegated behavior only in the other component's tests.

## Environment Variables

Create a `.env` file with:

```
VITE_API_BASE_URL=http://localhost:8080/api
```
