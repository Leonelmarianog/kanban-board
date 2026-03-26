<?php

namespace Modules\Infrastructure\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Modules\Application\UseCases\Auth\VerifyEmail\Exceptions\InvalidVerificationLinkException;
use Modules\Application\UseCases\Auth\VerifyEmail\VerifyEmailHandler;
use Modules\Application\UseCases\Auth\VerifyEmail\VerifyEmailRequestDto;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Http\Requests\Auth\VerifyEmailRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Email Verification',
    description: 'API endpoints for email verification'
)]
final class VerifyEmailController extends BaseController
{
    public function __construct(
        private readonly VerifyEmailHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/auth/email-verification/verify',
        description: 'Verify email address using the token, expires, and signature from the verification link',
        summary: 'Verify email address',
        tags: ['Email Verification'],
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
        responses: [
            new OA\Response(
                response: 200,
                description: 'Email verified successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Email verified successfully.'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid or expired verification link',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'status', type: 'integer', example: 400),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid or expired verification link.'),
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
    public function __invoke(VerifyEmailRequest $request): JsonResponse
    {
        $dto = new VerifyEmailRequestDto(
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
        } catch (InvalidVerificationLinkException) {
            return $this->error(
                statusCode: 400,
                message: 'Invalid or expired verification link.',
            );
        }
    }
}
