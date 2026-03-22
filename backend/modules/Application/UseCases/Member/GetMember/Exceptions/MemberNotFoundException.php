<?php

namespace Modules\Application\UseCases\Member\GetMember\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

final class MemberNotFoundException extends ApplicationException
{
    public function __construct(string $memberId)
    {
        parent::__construct(
            message: "Member with ID '{$memberId}' not found.",
            code: 404
        );
    }
}
