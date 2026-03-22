<?php

namespace Modules\Infrastructure\Http\Controllers\Member;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Application\UseCases\Member\GetMember\Exceptions\MemberNotFoundException;
use Modules\Application\UseCases\Member\GetMember\GetMemberHandler;
use Modules\Application\UseCases\Member\GetMember\GetMemberRequestDto;
use Modules\Infrastructure\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Members',
    description: 'API endpoints for member operations'
)]
final class GetMemberController extends BaseController
{
    public function __construct(
        private readonly GetMemberHandler $handler,
    ) {}

    #[OA\Get(
        path: '/api/v1/members/me',
        description: 'Returns the authenticated member profile',
        summary: 'Get current member profile',
        security: [['sanctum' => []]],
        tags: ['Members'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Member profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Member profile retrieved successfully.'),
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
                response: 404,
                description: 'Member not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 404),
                        new OA\Property(property: 'message', type: 'string', example: "Member with ID '...' not found."),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(
                statusCode: 401,
                message: 'Unauthenticated.',
            );
        }

        try {
            $requestDto = new GetMemberRequestDto(
                memberId: $user->id,
            );

            $response = $this->handler->execute($requestDto);

            return $this->success(
                message: 'Member profile retrieved successfully.',
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
        }
    }
}
