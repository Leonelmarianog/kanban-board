<?php

namespace Modules\Infrastructure\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Modules\Application\UseCases\Auth\RegisterUser\Exceptions\EmailAlreadyExistsException;
use Modules\Application\UseCases\Auth\RegisterUser\Exceptions\RegisterUserException;
use Modules\Application\UseCases\Auth\RegisterUser\Exceptions\UsernameAlreadyExistsException;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserHandler;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserRequestDto;
use Modules\Domain\Exceptions\ValidationDomainException;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Http\Requests\RegisterRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Authentication',
    description: 'API endpoints for user authentication'
)]
final class RegisterUserController extends BaseController
{
    public function __construct(
        private readonly RegisterUserHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/auth/register',
        description: 'Creates a new user account and returns an authentication token',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'password', 'password_confirmation', 'username'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
                    new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 201),
                        new OA\Property(property: 'message', type: 'string', example: 'Registration successful. Please check your email to verify your account.'),
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
                response: 409,
                description: 'Email or username already exists',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 409),
                        new OA\Property(property: 'message', type: 'string', example: "Email 'john@example.com' is already registered."),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 500),
                        new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred during registration.'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        try {
            $requestDto = new RegisterUserRequestDto(
                firstName: $request->validated('first_name'),
                lastName: $request->validated('last_name'),
                email: $request->validated('email'),
                password: $request->validated('password'),
                username: $request->validated('username'),
            );

            $response = $this->handler->execute($requestDto);

            return $this->success(
                message: $response->message,
                statusCode: 201,
            );
        } catch (ValidationDomainException $e) {
            return $this->error(
                statusCode: 422,
                message: $e->getMessage(),
            );
        } catch (UsernameAlreadyExistsException|EmailAlreadyExistsException $e) {
            return $this->error(
                statusCode: 409,
                message: $e->getMessage(),
            );
        } catch (RegisterUserException $e) {
            return $this->error(
                statusCode: 500,
                message: $e->getMessage(),
            );
        }
    }
}
