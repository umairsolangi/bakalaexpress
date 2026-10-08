<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('sellerType')->default(2)->comment('1 for admin, 2 for buyer, 3 for seller');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('mobile')->nullable()->comment('Mobile number of the user');
            $table->string('address')->nullable()->comment('Primary address line');
            $table->string('address2')->nullable()->comment('Secondary address line (optional)');
            $table->string('city')->nullable()->comment('City of residence');
            $table->string('state')->nullable()->comment('State of residence');
            $table->string('zip')->nullable()->comment('Postal code');
            $table->enum('pickup_time', ['morning', 'afternoon', 'evening'])->nullable()->comment('Preferred pickup time');
            $table->boolean('is_verified')->default(false)->comment('Indicates if the user has verified their email');
            $table->string('otp')->nullable()->comment('OTP for email verification');

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
}