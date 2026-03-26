<?php

namespace Modules\Infrastructure\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Modules\Application\UseCases\Auth\LoginUser\Exceptions\EmailNotVerifiedException;
use Modules\Application\UseCases\Auth\LoginUser\Exceptions\InvalidCredentialsException;
use Modules\Application\UseCases\Auth\LoginUser\Exceptions\LoginUserException;
use Modules\Application\UseCases\Auth\LoginUser\LoginUserHandler;
use Modules\Application\UseCases\Auth\LoginUser\LoginUserRequestDto;
use Modules\Domain\Exceptions\ValidationDomainException;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Http\Requests\LoginRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Authentication',
    description: 'API endpoints for user authentication'
)]
final class LoginUserController extends BaseController
{
    public function __construct(
        private readonly LoginUserHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/auth/login',
        description: 'Authenticates a user and returns an authentication token',
        summary: 'Login user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email_or_username', 'password'],
                properties: [
                    new OA\Property(property: 'email_or_username', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'token', type: 'string', example: '1|abcdef123456...'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 422),
                        new OA\Property(property: 'message', type: 'string', example: 'One or more validation errors occurred.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            additionalProperties: new OA\AdditionalProperties(
                                type: 'array',
                                items: new OA\Items(type: 'string')
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Email not verified',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 403),
                        new OA\Property(property: 'message', type: 'string', example: 'Please verify your email address before logging in.'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 500),
                        new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred during login.'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {
            $requestDto = new LoginUserRequestDto(
                emailOrUsername: $request->validated('email_or_username'),
                password: $request->validated('password'),
            );

            $response = $this->handler->execute($requestDto);

            return $this->success(
                message: 'Login successful.',
                statusCode: 200,
                data: ['token' => $response->token],
            );
        } catch (ValidationDomainException $e) {
            return $this->error(
                statusCode: 422,
                message: $e->getMessage(),
            );
        } catch (InvalidCredentialsException $e) {
            return $this->error(
                statusCode: 401,
                message: $e->getMessage(),
            );
        } catch (EmailNotVerifiedException $e) {
            return $this->error(
                statusCode: 403,
                message: $e->getMessage(),
            );
        } catch (LoginUserException $e) {
            return $this->error(
                statusCode: 500,
                message: $e->getMessage(),
            );
        }
    }
}
