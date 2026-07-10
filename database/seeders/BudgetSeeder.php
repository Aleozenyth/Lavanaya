<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Budget;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $allocations = [
            'PO' => 100_000_000,
            'ATK' => 5_000_000,
            'OPS' => 20_000_000,
            'TRV' => 15_000_000,
            'MKT' => 25_000_000,
        ];

        foreach ($allocations as $code => $amount) {
            $category = Category::where('code', $code)->first();
            if (! $category) {
                continue;
            }

            Budget::updateOrCreate(
                ['category_id' => $category->id, 'year' => $now->year, 'month' => $now->month],
                ['allocated_amount' => $amount, 'used_amount' => 0]
            );
        }
    }
}
