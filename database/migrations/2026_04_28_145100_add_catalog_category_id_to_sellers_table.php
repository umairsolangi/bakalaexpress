<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->foreignId('catalog_category_id')
                ->nullable()
                ->after('full_address')
                ->constrained('catalog_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('catalog_category_id');
        });
    }
};
