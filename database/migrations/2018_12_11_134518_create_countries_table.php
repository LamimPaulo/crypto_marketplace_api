<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('dial_code',10);
            $table->string('code', 4);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('country_id')->unsigned();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country_id');
        });
    }
}
