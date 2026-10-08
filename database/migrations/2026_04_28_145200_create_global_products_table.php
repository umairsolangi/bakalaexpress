<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_category_id')->constrained('catalog_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->string('unit_type')->nullable();
            $table->string('default_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['catalog_category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_products');
    }
};
