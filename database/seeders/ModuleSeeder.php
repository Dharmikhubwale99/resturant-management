<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppConfiguration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       AppConfiguration::firstOrCreate([
           'key' => 'category_module',
       ]);

       AppConfiguration::firstOrCreate([
            'key' => 'area_module',
    ]);
    }
}
