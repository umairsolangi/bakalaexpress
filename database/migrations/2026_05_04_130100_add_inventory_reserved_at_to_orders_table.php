<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'inventory_reserved_at')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('inventory_reserved_at')->nullable()->after('transaction_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('orders', 'inventory_reserved_at')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('inventory_reserved_at');
        });
    }
};
