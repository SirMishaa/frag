<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FragLinkRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'frag_file_id' => ['required', 'integer', 'exists:frag_files,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
