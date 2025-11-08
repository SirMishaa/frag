<?php

declare(strict_types=1);

namespace Database\Factories;

use App\MimeType;
use App\Models\FragFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FragFile>
 */
class FragFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mimeType = fake()->randomElement(MimeType::cases());
        $extension = $mimeType->extension();
        $filename = fake()->uuid().'.'.$extension;

        return [
            'user_id' => User::factory(),
            'filename' => $filename,
            'path' => 'user_'.fake()->randomNumber().'/'.$filename,
            'mime_type' => $mimeType,
            'size' => fake()->numberBetween(1024, 20971520), // 1KB to 20MB
            'checksum' => fake()->sha256(),
        ];
    }
}
