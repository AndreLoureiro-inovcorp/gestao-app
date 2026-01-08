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
        Schema::create('client_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->date('order_date')->nullable();
            $table->foreignId('client_id')->constrained('entities');
            $table->foreignId('proposal_id')->nullable()->constrained('proposals')->nullOnDelete();
            $table->enum('status', ['draft', 'closed'])->default('draft');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_orders');
    }
};
