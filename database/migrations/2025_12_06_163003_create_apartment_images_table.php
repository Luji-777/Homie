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
        Schema::create('apartment_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_cover')->default(false);

            // ضمان إنو ما يكون إلا صورة غلاف واحدة لكل شقة
            // $table->unique(['apartment_id', 'is_cover']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_images');
    }
};
