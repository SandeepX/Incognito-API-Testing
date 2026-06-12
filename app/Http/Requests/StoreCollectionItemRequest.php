<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'collection_id' => ['required', 'uuid', 'exists:collections,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:collection_items,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:folder,request'],
            'method' => ['required_if:type,request', 'in:GET,POST,PUT,PATCH,DELETE,HEAD,OPTIONS'],
            'url' => ['required_if:type,request', 'string'],
            'request_data' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Item name is required',
            'type.required' => 'Item type is required',
            'method.required_if' => 'HTTP method is required for request items',
            'url.required_if' => 'URL is required for request items',
        ];
    }
}
