<?php

namespace Modules\Infrastructure\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Modules\Application\UseCases\Auth\ConfirmEmailChange\ConfirmEmailChangeHandler;
use Modules\Application\UseCases\Auth\ConfirmEmailChange\ConfirmEmailChangeRequestDto;
use Modules\Application\UseCases\Auth\ConfirmEmailChange\Exceptions\InvalidEmailChangeTokenException;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Http\Requests\Auth\ConfirmEmailChangeRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Email Change',
    description: 'API endpoints for email change'
)]
final class ConfirmEmailChangeController extends BaseController
{
    public function __construct(
        private readonly ConfirmEmailChangeHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/auth/email-change/confirm',
        description: 'Confirm email change using the token, expires, and signature from the verification link',
        summary: 'Confirm email change',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'expires', 'signature'],
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'abc123...'),
                    new OA\Property(property: 'expires', type: 'integer', example: 1711497600),
                    new OA\Property(property: 'signature', type: 'string', example: 'def456...'),
                ]
            )
        ),
        tags: ['Email Change'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Email changed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Email changed successfully. Please log in with your new email.'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid or expired email change link',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'status', type: 'integer', example: 400),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid or expired email change link.'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'status', type: 'integer', example: 422),
                        new OA\Property(property: 'message', type: 'string', example: 'One or more validation errors occurred.'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(ConfirmEmailChangeRequest $request): JsonResponse
    {
        $dto = new ConfirmEmailChangeRequestDto(
            token: $request->validated('token'),
            expires: (int) $request->validated('expires'),
            signature: $request->validated('signature'),
        );

        try {
            $response = $this->handler->execute($dto);

            return $this->success(
                message: $response->message,
                statusCode: 200,
            );
        } catch (InvalidEmailChangeTokenException) {
            return $this->error(
                statusCode: 400,
                message: 'Invalid or expired email change link.',
            );
        }
    }
}
