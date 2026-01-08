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
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50);
            $table->date('invoice_date');
            $table->date('due_date');
            $table->foreignId('supplier_id')->constrained('entities');
            $table->foreignId('supplier_order_id')->nullable()->constrained('supplier_orders')->nullOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->string('document_path', 255)->nullable();
            $table->string('payment_proof_path', 255)->nullable();
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
