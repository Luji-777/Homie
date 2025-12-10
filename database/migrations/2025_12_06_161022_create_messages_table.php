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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            //-----------------------------
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');

            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            $table->text('message_content');
            $table->boolean('is_read')->default(false);

            $table->timestamps();

            // مؤشرات للسرعة (مهمة جدًا للـ inbox والمحادثات)
            $table->index(['apartment_id', 'sender_id', 'receiver_id']);
            $table->index('created_at');
            $table->index('is_read');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
