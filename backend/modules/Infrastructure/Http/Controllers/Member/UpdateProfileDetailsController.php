<?php

namespace Modules\Infrastructure\Http\Controllers\Member;

use Illuminate\Http\JsonResponse;
use Modules\Application\UseCases\Member\GetMember\Exceptions\MemberNotFoundException;
use Modules\Application\UseCases\Member\UpdateProfileDetails\Exceptions\UsernameAlreadyExistsException;
use Modules\Application\UseCases\Member\UpdateProfileDetails\UpdateProfileDetailsHandler;
use Modules\Application\UseCases\Member\UpdateProfileDetails\UpdateProfileDetailsRequestDto;
use Modules\Infrastructure\Http\Controllers\BaseController;
use Modules\Infrastructure\Http\Requests\Member\UpdateProfileDetailsRequest;
use Modules\Infrastructure\Persistence\Models\UserModel;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Members',
    description: 'API endpoints for member operations'
)]
final class UpdateProfileDetailsController extends BaseController
{
    public function __construct(
        private readonly UpdateProfileDetailsHandler $handler,
    ) {}

    #[OA\Put(
        path: '/api/v1/members/me/profile',
        description: 'Updates the authenticated member profile',
        summary: 'Update member profile',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'username'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                    new OA\Property(property: 'bio', type: 'string', example: 'Software developer', nullable: true),
                ]
            )
        ),
        tags: ['Members'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile updated successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', example: '550e8400-e29b-41d4-a716-446655440000'),
                                    new OA\Property(property: 'full_name', type: 'string', example: 'John Doe'),
                                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                    new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                                    new OA\Property(property: 'avatar_url', type: 'string', example: 'https://example.com/avatar.jpg', nullable: true),
                                    new OA\Property(property: 'bio', type: 'string', example: 'Software developer', nullable: true),
                                ]
                            )
                        ),
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
                response: 422,
                description: 'Validation error or username already taken',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 422),
                        new OA\Property(property: 'message', type: 'string', example: "Username 'johndoe' is already taken."),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(UpdateProfileDetailsRequest $request): JsonResponse
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
            $requestDto = new UpdateProfileDetailsRequestDto(
                memberId: $user->id,
                firstName: $request->validated('first_name'),
                lastName: $request->validated('last_name'),
                username: $request->validated('username'),
                bio: $request->validated('bio'),
            );

            $response = $this->handler->execute($requestDto);

            return $this->success(
                message: 'Profile updated successfully.',
                statusCode: 200,
                data: [
                    'id' => $response->id,
                    'full_name' => $response->fullName,
                    'email' => $response->email,
                    'username' => $response->username,
                    'avatar_url' => $response->avatarUrl,
                    'bio' => $response->bio,
                ],
            );
        } catch (MemberNotFoundException $e) {
            return $this->error(
                statusCode: 404,
                message: $e->getMessage(),
            );
        } catch (UsernameAlreadyExistsException $e) {
            return $this->error(
                statusCode: 422,
                message: $e->getMessage(),
            );
        }
    }
}
