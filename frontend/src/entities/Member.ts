import type { MemberInterface } from '@/entities/MemberInterface.ts';

export class Member implements MemberInterface {
  public constructor(
    public readonly id: string,
    public readonly full_name: string,
    public readonly initials: string,
    public readonly email: string,
    public readonly avatar_url?: string,
    public readonly bio?: string,
  ) {}

  public static create(data: MemberInterface): Member {
    return new Member(
      data.id,
      data.full_name,
      data.initials,
      data.email,
      data.avatar_url,
      data.bio,
    );
  }
}
