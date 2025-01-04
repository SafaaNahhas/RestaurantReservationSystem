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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('table_id')->constrained('tables')->cascadeOnDelete();
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->integer('guest_count');
            $table->text('services')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'in_service', 'completed'])->default('pending');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
            // $table->softDeletes();
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
