<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'urls' => ['required', 'array', 'min:1', 'max:50'],
            'urls.*' => ['required', 'url:http,https'],
            'selectors' => ['required', 'array', 'min:1'],
            'selectors.*' => ['required', 'string', 'max:255'],
            'container' => ['nullable', 'string', 'max:255'],
            'render_js' => ['nullable', 'boolean'],
        ];
    }
}
