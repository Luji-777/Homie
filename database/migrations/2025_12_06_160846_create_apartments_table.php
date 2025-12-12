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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->text('discription');
            $table->string('city');
            $table->string('governorate');
            $table->string('address');
            $table->float('price_per_day');
            $table->float('price_per_month');
            $table->json('specifications')->nullable(); // لتخزين مواصفات إضافية كـ JSON
            $table->integer('room');
            $table->boolean('wifi');
            $table->string('status');
            $table->boolean('is_available')->default(true); // يا هيك يا أما نحط الحالة تبعها بس الحالة حتكون string


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
