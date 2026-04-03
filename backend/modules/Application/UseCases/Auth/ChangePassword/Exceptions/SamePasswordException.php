<?php

namespace Modules\Application\UseCases\Auth\ChangePassword\Exceptions;

final class SamePasswordException extends ChangePasswordException
{
    public function __construct()
    {
        parent::__construct('The new password must be different from your current password.');
    }
}
