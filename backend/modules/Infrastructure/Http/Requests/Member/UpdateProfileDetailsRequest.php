<?php

namespace Modules\Infrastructure\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:1', 'max:255'],
            'last_name' => ['required', 'string', 'min:1', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
