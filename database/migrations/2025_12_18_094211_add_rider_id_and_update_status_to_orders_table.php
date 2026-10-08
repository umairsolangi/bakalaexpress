<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('rider_id')->nullable()->after('seller_id');
            $table->foreign('rider_id')->references('id')->on('riders')->onDelete('set null');

            // Reset existing statuses to 'pending' to avoid data truncation errors
            \Illuminate\Support\Facades\DB::table('orders')->update(['status' => 'pending']);

            // Modify status enum using raw SQL for MySQL. 
            // Note: This relies on the underlying DB being MySQL/MariaDB which is standard for this stack.
            // We include the full list of old and new statuses just in case, or just switch to the new list.
            // New flow: pending -> confirmed_by_seller -> preparing -> ready_for_pickup -> assigned_to_rider -> picked_up -> delivered -> cancelled

            if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                    'pending', 
                    'confirmed_by_seller', 
                    'preparing', 
                    'ready_for_pickup', 
                    'assigned_to_rider', 
                    'picked_up', 
                    'delivered', 
                    'cancelled',
                    'rejected'
                ) DEFAULT 'pending'");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['rider_id']);
            $table->dropColumn('rider_id');
            // Revert status enum is difficult without knowing exact previous state, skipping perfect revert for enum.
        });
    }
};
