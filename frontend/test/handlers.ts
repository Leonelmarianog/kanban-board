// https://mswjs.io/docs/best-practices/structuring-handlers/

import { http, HttpResponse } from 'msw';

// Test data
export const testUser = {
  id: 'user-123',
  full_name: 'Test User',
  initials: 'TU',
  email: 'test@example.com',
};

// Define happy path handlers only - override in tests for edge cases.
// Use wildcard origin (*) to match any BASE_URL from the environment.
export const handlers = [
  http.post('*/api/auth/register', () => {
    return HttpResponse.json({
      success: true,
      message: 'Registration successful',
      status: 201,
      data: { token: 'test-token-123' },
    });
  }),

  http.post('*/api/auth/login', () => {
    return HttpResponse.json({
      success: true,
      message: 'Login successful',
      status: 200,
      data: { token: 'test-token-123' },
    });
  }),

  http.post('*/api/auth/logout', () => {
    return HttpResponse.json({
      success: true,
      message: 'Logout successful',
      status: 200,
      data: [],
    });
  }),

  http.get('*/api/v1/members/me', () => {
    return HttpResponse.json({
      success: true,
      message: 'Member retrieved successfully',
      status: 200,
      data: [testUser],
    });
  }),
];
