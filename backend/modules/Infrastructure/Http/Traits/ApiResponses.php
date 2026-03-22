<?php

namespace Modules\Infrastructure\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    /**
     * Return an HTTP success response.
     *
     * @param  string  $message  The success message.
     * @param  int  $statusCode  The HTTP status code.
     * @param  array  $data  Optional data to include in the response (will be wrapped in an array).
     *
     * @phpstan-return JsonResponse
     *
     * @psalm-return JsonResponse
     *
     * @example Usage: $this->success('User created successfully.', 201, ['token' => 'abc']);
     * @example Result: { "status": 201, "message": "User created successfully.", "data": [{ "token": "abc" }] }
     */
    public function success(
        string $message,
        int $statusCode,
        array $data = []
    ): JsonResponse {
        $response = [
            'status' => $statusCode,
            'message' => $message,
            'data' => $data === [] ? [] : [$data],
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Return an HTTP error response.
     *
     * @param  int  $statusCode  The HTTP status code of the error.
     * @param  string  $message  A user-friendly error message.
     * @param  array<string, array<int, string>>  $errors  Optional validation errors in Laravel format:
     *                                                     `{ "field_name": ["message1", "message2"] }`.
     *
     * @phpstan-return JsonResponse
     *
     * @psalm-return JsonResponse
     *
     * @example Usage: $this->error(422, 'Validation failed.', ['email' => ['The email field is required.']]);
     * @example Result: { "status": 422, "message": "Validation failed.", "errors": { "email": ["The email field is required."] } }
     * @example Usage: $this->error(404, 'Resource not found.');
     * @example Result: { "status": 404, "message": "Resource not found." }
     */
    public function error(
        int $statusCode,
        string $message,
        array $errors = []
    ): JsonResponse {
        $response = [
            'status' => $statusCode,
            'message' => $message,
        ];

        if ($errors !== []) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
