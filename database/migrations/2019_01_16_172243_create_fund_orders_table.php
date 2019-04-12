<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fund_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->integer('fund_id');
            $table->enum('side', ['BUY', 'SELL']);
            $table->integer('quotes');
            $table->integer('quotes_executed');
            $table->decimal('admin_tax', 28,18)->default(0);
            $table->decimal('tax', 28,18)->default(0);
            $table->boolean('is_executed')->default(0);
            $table->decimal('value', 28, 18)->default(0);
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
        Schema::dropIfExists('fund_orders');
    }
}
