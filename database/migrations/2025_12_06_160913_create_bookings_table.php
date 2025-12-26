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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade'); // هاد بجوز مش ضروري لأنه فينا نجيب owner من الapartment
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->date('check_in');
            $table->date('check_out');
            $table->float('total_price')->nullable();
            $table->enum('status',[
                'pending',
                'owner _approved',
                'owner_rejected',
                'paid',
                'cancelled',
                'completed',
                ])->default('pending')->nullable();
            $table->text('cancellation_resion')->nullable();
            $table->boolean('owner_approval')->default(false)->nullable(); // موافقة المالك


            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
