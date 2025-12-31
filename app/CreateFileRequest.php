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
        $extensions = collect(MimeType::cases())
            ->map(fn (MimeType $type) => $type->extension())
            ->push('jpeg')
            ->unique()
            ->implode(',');

        $mimeTypes = collect(MimeType::cases())
            ->flatMap(fn (MimeType $type) => $type->mimeTypes())
            ->unique()
            ->implode(',');

        return [
            'file' => "required|file|max:20480|extensions:{$extensions}|mimetypes:{$mimeTypes}",
            'expires_at' => 'nullable|date|after:now',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
