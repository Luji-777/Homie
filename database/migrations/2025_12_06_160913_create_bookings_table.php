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
        // Schema::create('bookings', function (Blueprint $table) {
        //     $table->id();

        //     $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
        //     $table->foreignId('owner_id')->constrained('users')->onDelete('cascade'); // هاد بجوز مش ضروري لأنه فينا نجيب owner من الapartment
        //     $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
        //     $table->date('check_in');
        //     $table->date('check_out');
        //     $table->float('total_price')->nullable();
        //     $table->enum('status',[
        //         'pending',
        //         'owner_approved',
        //         'owner_rejected',
        //         'paid',
        //         'cancelled',
        //         'completed',
        //         ])->default('pending')->nullable();
        //     $table->text('cancellation_reason')->nullable();
        //     $table->boolean('owner_approval')->default(false)->nullable(); // موافقة المالك


        //     $table->timestamps();
        // });
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');

            $table->date('check_in');
            $table->date('check_out');
            $table->decimal('total_price', 12, 2);

            $table->enum('status', [
                'pending',
                'owner_approved',
                'owner_rejected',
                'cancelled',
                'completed',
            ])->default('pending');

            $table->enum('request_status', [
                
                'rent_request',
                'rent_rejected',
                'rent_approved',

                'cancellation_request',
                'cancellation_approved',
                'cancellation_rejected',

                'modification_request',
                'modification_approved',
                'modification_rejected',

                'completed',
            ])->default('rent_request');
            
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            
            // $table->decimal('refund_amount', 12, 2)->nullable();     // كم تم إرجاعه فعلياً
            // $table->integer('refund_percentage')->nullable();        // 100 أو 50 (أو 0 مستقبلاً)

            // // يمكن إبقاء هذا مؤقتاً للتوافق مع الكود القديم
            // $table->boolean('owner_approval')->default(false);

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
