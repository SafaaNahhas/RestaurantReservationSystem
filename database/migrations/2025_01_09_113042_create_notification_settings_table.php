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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('method_send_notification', ['mail', 'telegram']);
            $table->unsignedInteger('telegram_chat_id')->nullable();
            $table->json('reservation_send_notification');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }   
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
