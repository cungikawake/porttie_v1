<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListingProductClosed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listing_product_close', function (Blueprint $table) {
            
            $table->integer('id', true);
			$table->integer('listing_id')->nullable();
			$table->date('close_date')->nullable();
			$table->string('close_name',100)->nullable();
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listing_product_close');
    }
}
