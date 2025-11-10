<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FragFile;
use App\Models\FragLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<FragLink>
 */
class FragLinkFactory extends Factory
{
    protected $model = FragLink::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->slug(),
            'state' => $this->faker->randomElement(['active', 'revoked']),
            'expires_at' => $this->faker->optional()->dateTimeBetween('+1 day', '+1 week'),
            'password_hash' => $this->faker->optional(0.05)->password(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'frag_file_id' => FragFile::factory(),
            'user_id' => User::factory(),
        ];
    }
}
