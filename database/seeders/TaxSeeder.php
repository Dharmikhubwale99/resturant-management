<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaxSetting;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['name' => 'GST @ 0.25%', 'rate' => 0.25, 'is_active' => 0],
            ['name' => 'GST @ 1%', 'rate' => 1.00, 'is_active' => 0],
            ['name' => 'GST @ 3%', 'rate' => 3.00, 'is_active' => 0],
            ['name' => 'GST @ 5%', 'rate' => 5.00, 'is_active' => 0],
            ['name' => 'GST @ 12%', 'rate' => 12.00, 'is_active' => 0],
            ['name' => 'GST @ 18%', 'rate' => 18.00, 'is_active' => 0],
            ['name' => 'GST @ 28%', 'rate' => 28.00, 'is_active' => 0],
        ])->each(fn ($tax) =>
            TaxSetting::firstOrCreate(
                ['name' => $tax['name']],
                ['rate' => $tax['rate'], 'is_active' => $tax['is_active']]
            )
        );
    }
}
