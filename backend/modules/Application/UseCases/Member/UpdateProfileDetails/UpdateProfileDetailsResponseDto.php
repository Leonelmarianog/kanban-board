<?php

namespace Modules\Application\UseCases\Member\UpdateProfileDetails;

final readonly class UpdateProfileDetailsResponseDto
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
