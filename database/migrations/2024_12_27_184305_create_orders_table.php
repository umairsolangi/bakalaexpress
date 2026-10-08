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
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->string('address');
            $table->string('phone');
            $table->enum('status', ['pending', 'accepted', 'in_progress', 'completed', 'cancelled', 'rejected',
                'pickup_departed', 'picked_up', 'started_washing', 'ironing', 'ready_for_delivery', 'delivered'])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}; 
