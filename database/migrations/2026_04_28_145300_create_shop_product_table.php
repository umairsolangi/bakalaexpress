<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('global_product_id')->constrained('global_products')->cascadeOnDelete();
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['seller_id', 'global_product_id']);
            $table->index(['seller_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product');
    }
};
