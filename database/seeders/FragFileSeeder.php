<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FragFile;
use Illuminate\Database\Seeder;

class FragFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FragFile::factory()->count(10)->create();
    }
}
