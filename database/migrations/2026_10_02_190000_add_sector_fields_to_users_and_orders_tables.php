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
        Schema::table('users', function (Blueprint $table) {
            $table->string('sector', 10)->nullable()->after('zip');
            $table->string('near_area', 100)->nullable()->after('sector');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_sector', 10)->nullable()->after('delivery_instructions');
            $table->string('delivery_near_area', 100)->nullable()->after('delivery_sector');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sector', 'near_area']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_sector', 'delivery_near_area']);
        });
    }
};
