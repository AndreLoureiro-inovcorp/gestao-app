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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->boolean('all_day')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('calendar_type_id')->nullable()->constrained('calendar_types')->nullOnDelete();
            $table->foreignId('calendar_action_id')->nullable()->constrained('calendar_actions')->nullOnDelete();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
