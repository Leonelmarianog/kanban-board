<?php

namespace Modules\Application\UseCases\Member\GetMember;

use Modules\Application\UseCases\Member\GetMember\Exceptions\MemberNotFoundException;
use Modules\Domain\User\User;

final readonly class GetMemberHandler
{
    public function __construct(
        private GetMemberRepositoryInterface $repository,
    ) {}

    public function execute(GetMemberRequestDto $request): GetMemberResponseDto
    {
        $user = $this->repository->findById($request->memberId);

        if ($user === null) {
            throw new MemberNotFoundException($request->memberId);
        }

        return $this->toResponseDto($user);
    }

    private function toResponseDto(User $user): GetMemberResponseDto
    {
        return new GetMemberResponseDto(
            id: $user->getId(),
            fullName: $user->getFirstName()->getValue().' '.$user->getLastName()->getValue(),
            email: $user->getEmail()->getValue(),
            username: $user->getUsername()->getValue(),
            avatarUrl: $user->getPicture(),
            bio: $user->getBio(),
        );
    }
}
