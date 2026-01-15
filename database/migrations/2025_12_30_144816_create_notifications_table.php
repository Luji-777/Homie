<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reciver_id')->constrained('users')->onDelete('cascade'); //id reciver (tenant or owner)
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); //id sender (tenant or owner)

            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade')->nullable(); //id reciver (tenant or owner)
            $table->string('title');
            $table->text('body');
            // $table->string('type');
            //$table->unsignedBigInteger('related_id')->nullable();
            $table->boolean('is_read')->default(false)->nullable();
            $table->timestamp('read_at')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
