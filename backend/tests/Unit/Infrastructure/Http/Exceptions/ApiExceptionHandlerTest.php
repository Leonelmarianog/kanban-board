<?php

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Infrastructure\Http\Exceptions\ApiExceptionHandler;

describe('ApiExceptionHandler', function () {
    $handler = new ApiExceptionHandler;
    $request = new Request;

    describe('handleValidationException', function () use ($handler, $request) {
        it('returns validation errors in Laravel format', function () use ($handler, $request) {
            $errors = [
                'email' => ['The email field is required.', 'The email must be a valid email address.'],
                'password' => ['The password must be at least 8 characters.'],
            ];

            $exception = ValidationException::withMessages($errors);

            $response = $handler->handle($exception, $request);
            $data = $response->getData(true);

            expect($response->getStatusCode())->toBe(422)
                ->and($data['status'])->toBe(422)
                ->and($data['message'])->toBe('One or more validation errors occurred.')
                ->and($data['errors'])->toBe([
                    'email' => ['The email field is required.', 'The email must be a valid email address.'],
                    'password' => ['The password must be at least 8 characters.'],
                ]);
        });

        it('handles single field with single error', function () use ($handler, $request) {
            $errors = [
                'email' => ['The email field is required.'],
            ];

            $exception = ValidationException::withMessages($errors);

            $response = $handler->handle($exception, $request);
            $data = $response->getData(true);

            expect($data['errors'])->toBe([
                'email' => ['The email field is required.'],
            ]);
        });

        it('handles empty validation errors', function () use ($handler, $request) {
            $exception = ValidationException::withMessages([]);

            $response = $handler->handle($exception, $request);
            $data = $response->getData(true);

            expect($data)->not->toHaveKey('errors');
        });
    });

    describe('handleDefault', function () use ($handler, $request) {
        it('returns generic 500 error for unhandled exceptions', function () use ($handler, $request) {
            $exception = new Exception('Something went wrong');

            $response = $handler->handle($exception, $request);
            $data = $response->getData(true);

            expect($response->getStatusCode())->toBe(500)
                ->and($data['status'])->toBe(500)
                ->and($data['message'])->toBe('An unexpected error occurred.');
        });
    });
});
