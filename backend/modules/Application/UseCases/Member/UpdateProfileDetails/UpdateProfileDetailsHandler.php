<?php

namespace Modules\Application\UseCases\Member\UpdateProfileDetails;

use Modules\Application\UseCases\Member\GetMember\Exceptions\MemberNotFoundException;
use Modules\Application\UseCases\Member\UpdateProfileDetails\Exceptions\UsernameAlreadyExistsException;
use Modules\Domain\User\User;

final readonly class UpdateProfileDetailsHandler
{
    public function __construct(
        private UpdateProfileDetailsRepositoryInterface $repository,
    ) {}

    public function execute(UpdateProfileDetailsRequestDto $request): UpdateProfileDetailsResponseDto
    {
        $existingUser = $this->repository->findById($request->memberId);

        if (! $existingUser) {
            throw new MemberNotFoundException($request->memberId);
        }

        if ($request->username !== $existingUser->getUsername()->getValue()) {
            if ($this->repository->usernameExists($request->username, $request->memberId)) {
                throw new UsernameAlreadyExistsException($request->username);
            }
        }

        $existingUser->updateProfileDetails(
            firstName: $request->firstName,
            lastName: $request->lastName,
            username: $request->username,
            bio: $request->bio,
        );

        $updatedUser = $this->repository->update($existingUser);

        return $this->toResponseDto($updatedUser);
    }

    private function toResponseDto(User $user): UpdateProfileDetailsResponseDto
    {
        return new UpdateProfileDetailsResponseDto(
            id: $user->getId(),
            fullName: $user->getFirstName()->getValue().' '.$user->getLastName()->getValue(),
            email: $user->getEmail()->getValue(),
            username: $user->getUsername()->getValue(),
            avatarUrl: $user->getPicture(),
            bio: $user->getBio(),
        );
    }
}
