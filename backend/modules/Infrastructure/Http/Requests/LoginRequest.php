<?php

namespace Modules\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'email_or_username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
