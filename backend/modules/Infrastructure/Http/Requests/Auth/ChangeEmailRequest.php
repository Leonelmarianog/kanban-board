<?php

namespace Modules\Infrastructure\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class ChangeEmailRequest extends FormRequest
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
            'new_email' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
