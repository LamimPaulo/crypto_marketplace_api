<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNaviPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('navi_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->decimal('navi_quote',12,2)->default(0.25);
            $table->decimal('btc_quote',18,8);
            $table->decimal('usd_quote',13,3);
            $table->integer('total')->unsigned();
            $table->decimal('amount_btc', 18, 8);
            $table->decimal('amount_usd', 18, 8);
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('navi_payments');
    }
}
