<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Language;
use App\Models\FragCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FragCode>
 */
class FragCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $language = fake()->randomElement(Language::cases());

        return [
            'user_id' => User::factory(),
            'title' => fake()->optional()->sentence(),
            'code' => '<p>content</p>',
            'language' => $language,
        ];
    }
}
