<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month'); // 1-12, budget per bulan per kategori
            $table->decimal('allocated_amount', 18, 2);
            $table->decimal('used_amount', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
