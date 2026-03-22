<?php

namespace Modules\Application\UseCases\Member\GetMember;

final readonly class GetMemberRequestDto
{
    public function __construct(
        public string $memberId,
    ) {}
}
