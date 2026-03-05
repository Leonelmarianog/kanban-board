import type { BackendResponse, Member } from '@/api/backend/types.ts';
import { backendClient } from '@/api/backend/client.ts';

async function getMe(): Promise<BackendResponse<Member>> {
  return backendClient<Member>('/v1/members/me', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${localStorage.getItem('authToken') || ''}`,
    },
  });
}

export const memberApi = {
  getMe,
};
