<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FragCode;
use Illuminate\Database\Seeder;

class FragCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FragCode::factory()->count(10)->create();
    }
}
