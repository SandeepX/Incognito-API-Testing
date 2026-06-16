<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProxyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'method' => 'required|in:GET,POST,PUT,PATCH,DELETE,HEAD,OPTIONS',
            'url' => 'required|url|max:2048',
            'headers' => 'nullable|array',
            'headers.*.key' => 'nullable|string|max:255',
            'headers.*.value' => 'nullable|string|max:2048',
            'body' => 'nullable|string',
            'bodyType' => 'nullable|in:none,json,form-data',
            'formData' => 'nullable|array',
            'formData.*.key' => 'nullable|string|max:255',
            'formData.*.value' => 'nullable|string|max:2048',
        ];
    }
}
