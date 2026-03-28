<?php

namespace Modules\Infrastructure\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Modules\Application\UseCases\Auth\SendVerificationEmail\SendVerificationEmailHandler;
use Modules\Application\UseCases\Auth\SendVerificationEmail\SendVerificationEmailRequestDto;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Http\Requests\Auth\SendVerificationEmailRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Email Verification',
    description: 'API endpoints for email verification'
)]
final class SendVerificationEmailController extends BaseController
{
    public function __construct(
        private readonly SendVerificationEmailHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/auth/email-verification/send',
        description: 'Send a verification email to the provided email address',
        summary: 'Send verification email',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                ]
            )
        ),
        tags: ['Email Verification'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification email sent (or will be sent if email exists)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'If your email is registered, you will receive a verification link.'),
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
    public function __invoke(SendVerificationEmailRequest $request): JsonResponse
    {
        $dto = new SendVerificationEmailRequestDto(
            email: $request->validated('email'),
        );

        $response = $this->handler->execute($dto);

        return $this->success(
            message: $response->message,
            statusCode: 200,
        );
    }
}
