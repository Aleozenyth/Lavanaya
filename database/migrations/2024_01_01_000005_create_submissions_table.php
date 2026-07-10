<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->date('tanggal_pengajuan');
            $table->foreignId('user_id')->constrained('users'); // pengaju
            $table->foreignId('category_id')->constrained('categories');
            $table->decimal('nilai', 18, 2);
            $table->text('deskripsi')->nullable();
            $table->string('lampiran_path')->nullable();
            $table->string('lampiran_original_name')->nullable();

            // status workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'waiting_spv',
                'waiting_manager',
                'waiting_director',
                'waiting_finance',
                'paid',
                'rejected',
            ])->default('draft');

            $table->string('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
