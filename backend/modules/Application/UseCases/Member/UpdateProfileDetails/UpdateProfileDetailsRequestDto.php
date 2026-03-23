<?php

namespace Modules\Application\UseCases\Member\UpdateProfileDetails;

final readonly class UpdateProfileDetailsRequestDto
{
    public function __construct(
        public string $memberId,
        public string $firstName,
        public string $lastName,
        public string $username,
        public ?string $bio,
    ) {}
}
