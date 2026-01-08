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
        Schema::create('client_order_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_order_id')->constrained('client_orders')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('supplier_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_order_articles');
    }
};
