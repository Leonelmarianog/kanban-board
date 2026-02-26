import { Member } from '@/entities/Member.ts';
import type { MemberJsonInterface } from '@/api/backend/types.ts';
import { backendClient, BackendError } from '@/api/backend';
import { MemberServiceError } from '@/services/backend/member/MemberServiceError.ts';

const getMe = async (token: string): Promise<Member> => {
  try {
    const {
      data: [memberJson],
    } = await backendClient.request<MemberJsonInterface[]>('/v1/members/me', 'GET', undefined, {
      headers: { Authorization: `Bearer ${token}` },
    });

    return Member.create({
      id: memberJson!.id,
      full_name: memberJson!.full_name,
      initials: 'JD',
      email: memberJson!.email,
      avatar_url: memberJson!.avatar_url,
      bio: memberJson!.bio,
    });
  } catch (error) {
    if (error instanceof BackendError) {
      throw MemberServiceError.fromBackendError(error);
    }

    throw error;
  }
};

export const memberService = {
  getMe,
};
