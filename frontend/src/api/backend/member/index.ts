import type { Member } from '@/api/backend/types.ts';
import { backendClient } from '@/api/backend/client.ts';

async function getMe(): Promise<Member> {
  return backendClient<Member>('/v1/members/me', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      ...(localStorage.getItem('authToken') && {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`,
      }),
    },
  });
}

export const memberApi = {
  getMe,
};
