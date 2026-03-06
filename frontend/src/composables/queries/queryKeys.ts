/**
 * Query keys for member-related queries.
 */
export const memberKeys = {
  all: ['member'] as const,
  me: () => [...memberKeys.all, 'me'] as const,
} as const;
