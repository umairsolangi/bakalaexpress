<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersTable extends Migration
{
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('profile_image')->nullable()->comment('Path to seller profile image');
            $table->string('city')->nullable()->comment('City where seller operates');
            $table->string('area')->nullable()->comment('Area where seller operates');
            $table->integer('accountIsApproved')->unsigned()->comment('1 for yes, 0 for no');
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sellers');
    }
}
