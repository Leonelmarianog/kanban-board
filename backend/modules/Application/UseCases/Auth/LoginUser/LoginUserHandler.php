<?php

namespace Modules\Application\UseCases\Auth\LoginUser;

use Modules\Application\UseCases\Auth\LoginUser\Exceptions\InvalidCredentialsException;
use Modules\Application\UseCases\Auth\LoginUser\Exceptions\LoginUserException;

final readonly class LoginUserHandler
{
    public function __construct(
        private LoginUserRepositoryInterface $repository,
    ) {}

    public function execute(LoginUserRequestDto $request): LoginUserResponseDto
    {
        $user = $this->repository->findByEmailOrUsername($request->emailOrUsername);

        if ($user === null) {
            throw new InvalidCredentialsException;
        }

        if (! $user->getPassword()->verify($request->password)) {
            throw new InvalidCredentialsException;
        }

        $token = $this->repository->createToken($user, 'auth-token');

        if ($token === null) {
            throw new LoginUserException("Failed to generate token for user '{$user->getId()}' after successful login.", 500);
        }

        return new LoginUserResponseDto(
            token: $token,
        );
    }
}
