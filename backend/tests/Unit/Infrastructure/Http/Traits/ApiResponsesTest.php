<?php

use Illuminate\Http\JsonResponse;
use Modules\Infrastructure\Http\Traits\ApiResponses;

describe('ApiResponses trait', function () {
    $testClass = new class
    {
        use ApiResponses;
    };

    describe('success', function () use ($testClass) {
        it('returns data as array containing one object when data is provided', function () use ($testClass) {
            $response = $testClass->success(
                message: 'Operation successful.',
                statusCode: 201,
                data: ['token' => 'abc123', 'user_id' => 1]
            );

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(201)
                ->and($response->getData(true))->toBe([
                    'status' => 201,
                    'message' => 'Operation successful.',
                    'data' => [['token' => 'abc123', 'user_id' => 1]],
                ]);
        });

        it('returns empty data array when no data is provided', function () use ($testClass) {
            $response = $testClass->success(
                message: 'Operation successful.',
                statusCode: 200
            );

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(200)
                ->and($response->getData(true))->toBe([
                    'status' => 200,
                    'message' => 'Operation successful.',
                    'data' => [],
                ]);
        });

        it('returns empty data array when empty array is provided', function () use ($testClass) {
            $response = $testClass->success(
                message: 'Operation successful.',
                statusCode: 204,
                data: []
            );

            expect($response->getData(true)['data'])->toBe([]);
        });
    });

    describe('error', function () use ($testClass) {
        it('returns error response without errors', function () use ($testClass) {
            $response = $testClass->error(
                statusCode: 404,
                message: 'Resource not found.'
            );

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(404)
                ->and($response->getData(true))->toBe([
                    'status' => 404,
                    'message' => 'Resource not found.',
                ]);
        });

        it('returns error response with validation errors', function () use ($testClass) {
            $response = $testClass->error(
                statusCode: 422,
                message: 'Validation failed.',
                errors: [
                    'email' => ['The email field is required.'],
                    'password' => ['The password must be at least 8 characters.'],
                ]
            );

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(422)
                ->and($response->getData(true))->toBe([
                    'status' => 422,
                    'message' => 'Validation failed.',
                    'errors' => [
                        'email' => ['The email field is required.'],
                        'password' => ['The password must be at least 8 characters.'],
                    ],
                ]);
        });

        it('does not include errors key when errors array is empty', function () use ($testClass) {
            $response = $testClass->error(
                statusCode: 500,
                message: 'Internal server error.',
                errors: []
            );

            $data = $response->getData(true);

            expect($data)->not->toHaveKey('errors');
        });
    });
});
