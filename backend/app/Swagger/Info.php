<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

/** @phan-suppress PhanUndeclaredConstant */
#[OA\OpenApi(
    info: new OA\Info(
        version: '1.0.0',
        description: 'API documentation for the Kanban Board backend application',
        title: 'Kanban Board API',
        contact: new OA\Contact(email: 'admin@example.com')
    ),
    servers: [
        new OA\Server(
            url: L5_SWAGGER_CONST_HOST, // @phpstan-ignore constant.notFound
            description: 'API Server'
        ),
    ],
    components: new OA\Components(
        responses: [
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Authentication token is missing or invalid',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Insufficient permissions',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 403),
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found - Resource does not exist',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 404),
                        new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal Server Error - Unexpected error occurred',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', example: 500),
                        new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred.'),
                    ]
                )
            ),
        ]
    )
)]
class Info {}
