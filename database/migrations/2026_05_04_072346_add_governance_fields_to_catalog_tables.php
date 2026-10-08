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
        Schema::table('global_products', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_tables', function (Blueprint $table) {
            //
        });
    }
};
