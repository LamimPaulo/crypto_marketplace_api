<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('days_off')->default('0,6');
            $table->time('fri_close_time')->default('20:00:00');
            $table->time('mon_opening_time')->default('08:00:00');
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
        Schema::dropIfExists('support_configs');
    }
}
