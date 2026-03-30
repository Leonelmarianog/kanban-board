<?php

namespace Modules\Infrastructure\Http\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Infrastructure\Http\Traits\ApiResponses;
use Throwable;

final class ApiExceptionHandler
{
    use ApiResponses;

    /**
     * @var array<string, string> Mapping of exception classes to handler methods.
     */
    private array $handlers = [
        AuthenticationException::class => 'handleAuthenticationException',
        ValidationException::class => 'handleValidationException',
    ];

    /**
     * Handle the given exception by calling the appropriate handler method.
     *
     * @param  Throwable  $exception  The exception to handle.
     * @param  Request  $request  The incoming HTTP request.
     *
     * @phpstan-return JsonResponse
     *
     * @psalm-return JsonResponse
     */
    public function handle(Throwable $exception, Request $request): JsonResponse
    {
        foreach ($this->handlers as $exceptionClass => $handlerMethod) {
            if ($exception instanceof $exceptionClass) {
                return $this->$handlerMethod($exception, $request);
            }
        }

        return $this->handleDefault($exception, $request);
    }

    /**
     * Return a custom JSON response for validation exceptions.
     *
     * @param  ValidationException  $exception  The validation exception to handle.
     * @param  Request  $request  The incoming HTTP request.
     *
     * @phpstan-return JsonResponse
     *
     * @psalm-return JsonResponse
     */
    private function handleValidationException(
        ValidationException $exception,
        Request $request
    ): JsonResponse {
        return $this->error(
            statusCode: $exception->status,
            message: 'One or more validation errors occurred.',
            errors: $exception->errors()
        );
    }

    /**
     * Return a custom JSON response for authentication exceptions.
     *
     * @param  AuthenticationException  $exception  The authentication exception to handle.
     * @param  Request  $request  The incoming HTTP request.
     *
     * @phpstan-return JsonResponse
     *
     * @psalm-return JsonResponse
     */
    private function handleAuthenticationException(
        AuthenticationException $exception,
        Request $request
    ): JsonResponse {
        return $this->error(
            statusCode: 401,
            message: 'Unauthenticated.'
        );
    }

    /**
     * Return a generic error response for unhandled exceptions.
     *
     * @param  Throwable  $exception  The exception to handle.
     * @param  Request  $request  The incoming HTTP request.
     *
     * @phpstan-return JsonResponse
     *
     * @psalm-return JsonResponse
     */
    private function handleDefault(
        Throwable $exception,
        Request $request
    ): JsonResponse {
        logger()->error($exception);

        return $this->error(
            statusCode: 500,
            message: 'An unexpected error occurred.'
        );
    }
}
