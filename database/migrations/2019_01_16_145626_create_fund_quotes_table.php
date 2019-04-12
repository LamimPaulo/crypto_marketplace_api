<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFundQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fund_quotes', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->integer('fund_id')->unsigned();
            $table->integer('quote')->default(0);
            $table->decimal('value', 18,8)->default(0);
            $table->decimal('amount', 18,8)->default(0);
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
        Schema::dropIfExists('fund_quotes');
    }
}
