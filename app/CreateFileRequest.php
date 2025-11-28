<?php

declare(strict_types=1);

namespace App;

use Illuminate\Foundation\Http\FormRequest;

class CreateFileRequest extends FormRequest
{
    /**
     * @return string[]
     */
    public function rules(): array
    {
        $mimes = collect(MimeType::cases())
            ->map(fn (MimeType $type) => $type->extension())
            ->push('jpeg')
            ->unique()
            ->implode(',');

        return [
            'file' => "required|file|max:20480|mimes:{$mimes}", // max 20MB
            'expires_at' => 'nullable|date|after:now',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
