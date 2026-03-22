<?php

namespace Modules\Infrastructure\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Application\UseCases\Auth\LogoutUser\Exceptions\LogoutUserException;
use Modules\Application\UseCases\Auth\LogoutUser\LogoutUserHandler;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Persistence\Models\UserModel;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Authentication',
    description: 'API endpoints for user authentication'
)]
final class LogoutUserController extends BaseController
{
    public function __construct(
        private readonly LogoutUserHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/auth/logout',
        description: 'Revokes the current access token for the authenticated user',
        summary: 'Logout user',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Logout successful.'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 500),
                        new OA\Property(property: 'message', type: 'string', example: 'Failed to logout.'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            /** @var UserModel|null $user */
            $user = $request->user();

            if ($user === null) {
                return $this->error(
                    statusCode: 401,
                    message: 'Unauthenticated.',
                );
            }

            /** @var PersonalAccessToken|null $token */
            $token = $user->currentAccessToken();

            if ($token === null) {
                return $this->error(
                    statusCode: 401,
                    message: 'No active token found.',
                );
            }

            $this->handler->execute(
                userId: $user->id,
                tokenId: $token->id,
            );

            return $this->success(
                message: 'Logout successful.',
                statusCode: 200,
            );
        } catch (LogoutUserException $e) {
            return $this->error(
                statusCode: $e->getCode(),
                message: $e->getMessage(),
            );
        }
    }
}
