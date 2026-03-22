<?php

namespace Modules\Application\UseCases\Member\GetMember;

final readonly class GetMemberResponseDto
{
    public function __construct(
        public string $id,
        public string $fullName,
        public string $email,
        public string $username,
        public ?string $avatarUrl,
        public ?string $bio,
    ) {}
}
