<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateToListingVariantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('listing_variants', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable();
            $table->dateTime('finish_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('listing_variants', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('finish_date');
        });
    }
}
