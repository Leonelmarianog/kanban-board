<?php

namespace Modules\Application\UseCases\Auth\ChangePassword\Exceptions;

final class InvalidCurrentPasswordException extends ChangePasswordException
{
    public function __construct()
    {
        parent::__construct('The current password is incorrect.');
    }
}
