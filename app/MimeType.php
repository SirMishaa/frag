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

    public function extension(): string
    {
        return match ($this) {
            self::Png => 'png',
            self::Jpg => 'jpg',
            self::Gif => 'gif',
            self::Mp4 => 'mp4',
            self::Webp => 'webp',
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
            default => null,
        };
    }
}
