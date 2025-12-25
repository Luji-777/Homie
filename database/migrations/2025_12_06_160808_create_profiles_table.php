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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // $table->foreignId('area_id')->constrained('areas');
            // $table->foreignId('city_id')->constrained('cities');
            // $table->unsignedBigInteger('area_id')->nullable();

            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');


            $table->string('id_photo')->nullable();
            $table->string('personal_photo')->nullable();
            $table->string('profile_photo')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
