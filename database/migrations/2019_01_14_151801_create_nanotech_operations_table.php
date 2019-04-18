<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNanotechOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nanotech_operations', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');//user owner
            $table->integer('investment_id')->unsigned();//investments
            $table->decimal('amount', 28, 18);
            $table->decimal('brokerage_fee', 28, 18)->default(0);
            $table->decimal('brokerage_fee_percentage', 10, 8)->default(0);
            $table->decimal('profit_percent', 10, 8)->default(0);
            $table->integer('type')->unsigned();//EnumInvestmentOperationType
            $table->integer('status')->unsigned()->default(1);
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
        Schema::dropIfExists('nanotech_operations');
    }
}
