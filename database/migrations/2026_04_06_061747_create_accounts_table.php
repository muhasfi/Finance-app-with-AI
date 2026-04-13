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
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->enum('type', ['cash', 'bank', 'ewallet', 'credit', 'savings']);
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('IDR');
            $table->string('color', 7)->default('#6366f1');
            $table->string('icon')->default('building-library');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->unsignedTinyInteger('due_date_day')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
