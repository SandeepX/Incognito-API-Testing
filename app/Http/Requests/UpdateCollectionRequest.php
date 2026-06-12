<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'workspace_id' => ['nullable', 'uuid', 'exists:workspaces,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'workspace_id.exists' => 'The selected workspace does not exist',
        ];
    }
}
