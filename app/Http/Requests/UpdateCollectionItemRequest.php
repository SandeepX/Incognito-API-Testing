<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollectionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'uuid', 'exists:collection_items,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'method' => ['sometimes', 'in:GET,POST,PUT,PATCH,DELETE,HEAD,OPTIONS'],
            'url' => ['sometimes', 'string'],
            'request_data' => ['sometimes', 'array'],
        ];
    }
}
