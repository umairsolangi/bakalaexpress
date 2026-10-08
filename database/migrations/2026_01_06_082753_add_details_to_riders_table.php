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
        Schema::table('riders', function (Blueprint $table) {
            $table->string('cnic_number')->unique()->after('phone');
            $table->string('vehicle_number')->after('vehicle_type');
            $table->text('address')->after('email');
            $table->string('profile_image')->nullable()->after('password');
            $table->string('cnic_front')->nullable()->after('cnic_number');
            $table->string('cnic_back')->nullable()->after('cnic_front');
            $table->string('license_image')->nullable()->after('vehicle_number');
            $table->string('vehicle_image')->nullable()->after('license_image');
            $table->string('registration_book')->nullable()->after('vehicle_image');
            $table->boolean('is_approved')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn([
                'cnic_number',
                'vehicle_number',
                'address',
                'profile_image',
                'cnic_front',
                'cnic_back',
                'license_image',
                'vehicle_image',
                'registration_book',
                'is_approved',
            ]);
        });
    }
};
