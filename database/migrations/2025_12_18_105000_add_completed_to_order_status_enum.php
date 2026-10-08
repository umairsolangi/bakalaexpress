<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                    'pending', 
                    'confirmed_by_seller', 
                    'preparing', 
                    'ready_for_pickup', 
                    'assigned_to_rider', 
                    'picked_up', 
                    'delivered', 
                    'cancelled',
                    'rejected',
                    'completed'
                ) DEFAULT 'pending'");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting would involve removing 'completed', which might cause data loss if any orders are completed.
        // For now, we can leave it or revert to the previous list.
    }
};
