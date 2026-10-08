<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('sector', 10)->nullable()->after('area');
            $table->json('near_areas')->nullable()->after('sector');
            $table->text('full_address')->nullable()->after('near_areas');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['sector', 'near_areas', 'full_address']);
        });
    }
};
