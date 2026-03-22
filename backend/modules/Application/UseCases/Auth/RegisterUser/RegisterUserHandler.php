<?php

namespace Modules\Application\UseCases\Auth\RegisterUser;

use Illuminate\Support\Str;
use Modules\Application\UseCases\Auth\RegisterUser\Exceptions\EmailAlreadyExistsException;
use Modules\Application\UseCases\Auth\RegisterUser\Exceptions\RegisterUserException;
use Modules\Application\UseCases\Auth\RegisterUser\Exceptions\UsernameAlreadyExistsException;
use Modules\Domain\User\User;
use Modules\Domain\ValueObjects\HashedPassword;

final readonly class RegisterUserHandler
{
    public function __construct(
        private RegisterUserRepositoryInterface $repository,
    ) {}

    public function execute(RegisterUserRequestDto $request): RegisterUserResponseDto
    {
        if ($this->repository->emailExists($request->email)) {
            throw new EmailAlreadyExistsException($request->email);
        }

        if ($this->repository->usernameExists($request->username)) {
            throw new UsernameAlreadyExistsException($request->username);
        }

        $user = User::create(
            id: Str::uuid()->toString(),
            firstName: $request->firstName,
            lastName: $request->lastName,
            email: $request->email,
            password: HashedPassword::fromPlainText($request->password),
            username: $request->username,
        );

        $savedUser = $this->repository->save($user);

        $token = $this->repository->createToken($savedUser, 'auth-token');

        if ($token === null) {
            throw new RegisterUserException("Failed to generate token for user '{$savedUser->getId()}' after successful registration.");
        }

        return new RegisterUserResponseDto(
            token: $token,
        );
    }
}
