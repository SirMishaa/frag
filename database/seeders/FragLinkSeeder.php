<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FragLink;
use Illuminate\Database\Seeder;

class FragLinkSeeder extends Seeder
{
    public function run(): void
    {
        FragLink::factory()->count(10)->create();
    }
}
