<?php

namespace Modules\Infrastructure\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Modules\Application\UseCases\Auth\ChangeEmail\ChangeEmailHandler;
use Modules\Application\UseCases\Auth\ChangeEmail\ChangeEmailRequestDto;
use Modules\Application\UseCases\Auth\ChangeEmail\Exceptions\EmailAlreadyInUseException;
use Modules\Application\UseCases\Auth\ChangeEmail\Exceptions\SameEmailException;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Http\Requests\Auth\ChangeEmailRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Email Change',
    description: 'API endpoints for email change'
)]
final class ChangeEmailController extends BaseController
{
    public function __construct(
        private readonly ChangeEmailHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/auth/email-change',
        description: 'Request to change the authenticated user\'s email address',
        summary: 'Request email change',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['new_email'],
                properties: [
                    new OA\Property(property: 'new_email', type: 'string', format: 'email', example: 'new@example.com'),
                ]
            )
        ),
        tags: ['Email Change'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification email sent to new address',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'A verification email has been sent to your new email address.'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'status', type: 'integer', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Email already in use',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'status', type: 'integer', example: 409),
                        new OA\Property(property: 'message', type: 'string', example: 'The email \'new@example.com\' is already in use by another account.'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error or same email',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'status', type: 'integer', example: 422),
                        new OA\Property(property: 'message', type: 'string', example: 'The new email must be different from your current email.'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(ChangeEmailRequest $request): JsonResponse
    {
        $dto = new ChangeEmailRequestDto(
            userId: $request->user()->id,
            newEmail: $request->validated('new_email'),
        );

        try {
            $response = $this->handler->execute($dto);

            return $this->success(
                message: $response->message,
                statusCode: 200,
            );
        } catch (SameEmailException $e) {
            return $this->error(
                statusCode: 422,
                message: $e->getMessage(),
            );
        } catch (EmailAlreadyInUseException $e) {
            return $this->error(
                statusCode: 409,
                message: $e->getMessage(),
            );
        }
    }
}
