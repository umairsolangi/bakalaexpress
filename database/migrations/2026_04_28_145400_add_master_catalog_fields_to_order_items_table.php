<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order_items', 'shop_product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('shop_product_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('shop_product')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('order_items', 'global_product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('global_product_id')
                    ->nullable()
                    ->after('shop_product_id')
                    ->constrained('global_products')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('order_items', 'item_name')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('item_name')->nullable()->after('global_product_id');
            });
        }

        if (!Schema::hasColumn('order_items', 'unit_type')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('unit_type')->nullable()->after('item_name');
            });
        }

        if (!Schema::hasColumn('order_items', 'item_image')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('item_image')->nullable()->after('unit_type');
            });
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });

        if (Schema::hasColumn('order_items', 'shop_product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shop_product_id');
            });
        }

        if (Schema::hasColumn('order_items', 'global_product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('global_product_id');
            });
        }

        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('order_items', 'item_name') ? 'item_name' : null,
            Schema::hasColumn('order_items', 'unit_type') ? 'unit_type' : null,
            Schema::hasColumn('order_items', 'item_image') ? 'item_image' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table('order_items', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
