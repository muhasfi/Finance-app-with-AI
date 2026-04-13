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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('category_id')->nullable();
            $table->uuid('transfer_pair_id')->nullable();
            $table->uuid('recurring_plan_id')->nullable();
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_base', 15, 2);
            $table->string('currency', 3)->default('IDR');
            $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            $table->date('date');
            $table->text('note')->nullable();
            $table->json('tags')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('import_hash', 32)->nullable()->unique('unique_import_hash');
            $table->boolean('ai_categorized')->default(false);
            $table->unsignedTinyInteger('ai_confidence')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('transfer_pair_id')->references('id')->on('transactions')->nullOnDelete();
            $table->foreign('recurring_plan_id')->references('id')->on('recurring_plans')->nullOnDelete();
            $table->index(['account_id', 'date']);
            $table->index(['account_id', 'type', 'date']);
            $table->index(['category_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
