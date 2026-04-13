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
            $table->uuid('user_id');
            $table->uuid('category_id');
            $table->decimal('amount', 15, 2);
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('alert_threshold')->default(80);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
 
            $table->foreign('user_id', 'budgets_user_fk')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('category_id', 'budgets_category_fk')->references('id')->on('categories')->cascadeOnDelete();
            $table->unique(['user_id', 'category_id', 'month', 'year'], 'budgets_unique');
            $table->index(['user_id', 'month', 'year'], 'budgets_index');
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
