<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('seller_id'); 
            $table->string('service_name'); 
            $table->text('service_description')->nullable(); 
            $table->string('seller_city');
            $table->string('seller_area'); 
            $table->string('availability'); 
            $table->string('service_delivery_time'); 
            $table->string('seller_contact_no'); 
            $table->decimal('service_price', 10, 2); 
            $table->string('image')->nullable(); 
            $table->boolean('is_approved')->default(false);
            $table->timestamps(); 

            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
};
