<?php

namespace Modules\Infrastructure\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Application\UseCases\Auth\ChangePassword\ChangePasswordHandler;
use Modules\Application\UseCases\Auth\ChangePassword\ChangePasswordRequestDto;
use Modules\Application\UseCases\Auth\ChangePassword\Exceptions\InvalidCurrentPasswordException;
use Modules\Application\UseCases\Auth\ChangePassword\Exceptions\SamePasswordException;
use Modules\Application\UseCases\Auth\ChangePassword\Exceptions\UserNotFoundException;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Http\Requests\Auth\ChangePasswordRequest;
use Modules\Infrastructure\Persistence\Models\UserModel;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Password',
    description: 'API endpoints for password management'
)]
final class ChangePasswordController extends BaseController
{
    public function __construct(
        private readonly ChangePasswordHandler $handler,
    ) {}

    #[OA\Patch(
        path: '/api/auth/change-password',
        description: 'Change the authenticated user\'s password. All other sessions will be logged out.',
        summary: 'Change password',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(
                        property: 'current_password',
                        type: 'string',
                        example: 'currentpassword123'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'newpassword123'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        example: 'newpassword123'
                    ),
                ]
            )
        ),
        tags: ['Password'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password changed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 200
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Password changed successfully. You have been logged out from other devices.'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 401
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Unauthenticated.'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 404
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: "User with ID 'xxx' not found."
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid current password, same password, or validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 422
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'The current password is incorrect.'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(ChangePasswordRequest $request): JsonResponse
    {
        /** @var UserModel|null $user */
        $user = $request->user();

        if ($user === null) {
            return $this->error(
                statusCode: 401,
                message: 'Unauthenticated.',
            );
        }

        try {
            /** @var PersonalAccessToken $token */
            $token = $user->currentAccessToken();

            $requestDto = new ChangePasswordRequestDto(
                userId: $user->id,
                currentPassword: $request->validated('current_password'),
                newPassword: $request->validated('password'),
                currentTokenId: (string) $token->id,
            );

            $response = $this->handler->execute($requestDto);

            return $this->success(
                message: $response->message,
                statusCode: 200,
            );
        } catch (InvalidCurrentPasswordException|SamePasswordException $e) {
            return $this->error(
                statusCode: 422,
                message: $e->getMessage(),
            );
        } catch (UserNotFoundException $e) {
            return $this->error(
                statusCode: 404,
                message: $e->getMessage(),
            );
        }
    }
}
