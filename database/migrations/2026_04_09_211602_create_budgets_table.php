<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
 
            $table->decimal('amount', 15, 2);       // batas anggaran
            $table->unsignedTinyInteger('month');   // 1-12
            $table->unsignedSmallInteger('year');
 
            // Alert threshold — kirim notif saat pengeluaran mencapai X% dari budget
            $table->unsignedTinyInteger('alert_threshold')->default(80); // 80%
 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
 
            // Satu kategori hanya boleh punya satu budget per bulan per user
            $table->unique(['user_id', 'category_id', 'month', 'year']);
 
            $table->index(['user_id', 'month', 'year']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
