<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'workspace_id' => ['nullable', 'uuid', 'exists:workspaces,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Collection name is required',
            'name.max' => 'Collection name must not exceed 255 characters',
            'workspace_id.exists' => 'The selected workspace does not exist',
        ];
    }
}
