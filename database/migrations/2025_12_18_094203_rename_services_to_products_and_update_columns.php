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
        Schema::rename('services', 'products');

        Schema::table('products', function (Blueprint $table) {
            // Drop laundry specific or unnecessary columns
            $table->dropColumn(['service_name', 'service_description', 'service_delivery_time', 'service_price', 'availability']);

            // Add grocery specific columns
            $table->string('name')->after('seller_id');
            $table->text('description')->nullable()->after('name');
            $table->decimal('price', 10, 2)->after('description');
            $table->integer('stock_quantity')->default(0)->after('price');
            $table->string('unit_type')->nullable()->after('stock_quantity'); // e.g., kg, pcs, liters
            $table->unsignedBigInteger('category_id')->nullable()->after('unit_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'price', 'stock_quantity', 'unit_type', 'category_id']);
            $table->string('service_name');
            $table->text('service_description')->nullable();
            $table->string('service_delivery_time');
            $table->decimal('service_price', 10, 2);
            $table->string('availability');
        });

        Schema::rename('products', 'services');
    }
};
