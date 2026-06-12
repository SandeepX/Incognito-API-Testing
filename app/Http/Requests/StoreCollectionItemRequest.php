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
            'type'                  => 'required|in:folder,request',
            'name'                  => 'required|string|max:255',
            'parent_id'             => 'nullable|string',
            'request_data'          => 'nullable|array',
            'request_data.method'   => 'nullable|string',
            'request_data.url'      => 'nullable|string',
            'response_data'         => 'nullable|array',
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
