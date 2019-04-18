<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNanotechTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nanotech_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->unique();
            $table->decimal('brokerage_fee', 10, 8)->default(0);//per operation
            $table->decimal('montly_return', 10, 8)->default(0);
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
        Schema::dropIfExists('nanotech_types');
    }
}
