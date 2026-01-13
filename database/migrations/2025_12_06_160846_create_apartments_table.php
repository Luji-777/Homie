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
            $table->enum('type',[
                'room',
                'studio',
                'house',
                'villa'
            ]);
            $table->text('discription');                                                
            $table->string('title');
            $table->string('address');

            $table->enum('rent_type',[
                'day',
                'month'
            ]);
            $table->float('price');
            $table->float('space');
            $table->string('floor');
            $table->integer('rooms');
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->boolean('wifi');           
            $table->boolean('solar');

            // $table->boolean('garage');
            // $table->json('specifications')->nullable(); // لتخزين مواصفات إضافية كـ JSON
            
            $table->boolean('is_approved')->nullable()->default(false);
            $table->string('status')->nullable();
            $table->boolean('is_available')->default(true)->nullable(); // يا هيك يا أما نحط الحالة تبعها بس الحالة حتكون string


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
