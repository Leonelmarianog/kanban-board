<?php

namespace Modules\Application\UseCases\Auth\ChangePassword\Exceptions;

final class UserNotFoundException extends ChangePasswordException
{
    public function __construct(string $userId)
    {
        parent::__construct("User with ID '{$userId}' not found.");
    }
}
