<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'PO Produk', 'code' => 'PO'],
            ['name' => 'ATK', 'code' => 'ATK'],
            ['name' => 'Operasional', 'code' => 'OPS'],
            ['name' => 'Perjalanan Dinas', 'code' => 'TRV'],
            ['name' => 'Marketing', 'code' => 'MKT'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['code' => $cat['code']], $cat + ['is_active' => true]);
        }
    }
}
