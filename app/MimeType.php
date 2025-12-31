<?php

declare(strict_types=1);

namespace App;

enum MimeType: string
{
    case Png = 'image/png';
    case Jpg = 'image/jpeg';
    case Gif = 'image/gif';
    case Mp4 = 'video/mp4';
    case Webp = 'image/webp';
    case Obj = 'application/prs.wavefront-obj'; // Official IANA MIME type for Wavefront .obj files

    public function extension(): string
    {
        return match ($this) {
            self::Png => 'png',
            self::Jpg => 'jpg',
            self::Gif => 'gif',
            self::Mp4 => 'mp4',
            self::Webp => 'webp',
            self::Obj => 'obj',
        };
    }

    /**
     * Get all possible MIME types for this file type.
     *
     * @return string[]
     */
    public function mimeTypes(): array
    {
        return match ($this) {
            self::Png => ['image/png'],
            self::Jpg => ['image/jpeg'],
            self::Gif => ['image/gif'],
            self::Mp4 => ['video/mp4'],
            self::Webp => ['image/webp'],
            // .obj files can have multiple MIME types depending on the system
            self::Obj => [
                'application/prs.wavefront-obj', // Official IANA MIME type
                'text/plain', // Common detection as .obj files are text-based
                'application/object',
                'application/octet-stream',
                'model/obj',
            ],
        };
    }

    public static function fromExtension(string $extension): ?self
    {
        return match (strtolower($extension)) {
            'png' => self::Png,
            'jpg', 'jpeg' => self::Jpg,
            'gif' => self::Gif,
            'mp4' => self::Mp4,
            'webp' => self::Webp,
            'obj' => self::Obj,
            default => null,
        };
    }

    /**
     * Get the canonical MimeType enum from any valid MIME type string.
     * This normalizes variant MIME types to their canonical backing value.
     */
    public static function fromMimeType(string $mimeType): ?self
    {
        foreach (self::cases() as $case) {
            if (in_array($mimeType, $case->mimeTypes(), true)) {
                return $case;
            }
        }

        return null;
    }
}
