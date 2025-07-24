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
        $modules = [
            'category_module',
            'area_module',
            'expense-type-module',
            'user'
        ];

        foreach ($modules as $key) {
            AppConfiguration::firstOrCreate(['key' => $key]);
        }
    }
}
