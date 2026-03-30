<?php

namespace Modules\Infrastructure\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CancelEmailChangeRequest',
    required: ['token', 'expires', 'signature'],
    properties: [
        new OA\Property(property: 'token', type: 'string', example: 'abc123...'),
        new OA\Property(property: 'expires', type: 'integer', example: 1711497600),
        new OA\Property(property: 'signature', type: 'string', example: 'def456...'),
    ]
)]
final class CancelEmailChangeRequest extends FormRequest
{
    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'size:64'],
            'expires' => ['required', 'integer'],
            'signature' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
