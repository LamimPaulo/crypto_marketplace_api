<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBalanceHistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_balance_hists', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('wallet_id');
            $table->uuid('user_id');
            $table->integer('coin_id')->unsigned();
            $table->string('address')->nullable();
            $table->uuid('transaction_id');
            $table->decimal('amount', 18, 8)->default(0);
            $table->decimal('fee', 18, 8)->default(0);
            $table->decimal('tax', 18, 8)->default(0);
            $table->decimal('balance', 18, 8)->default(0)->unsigned();
            $table->string('type')->default('increments');
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
        Schema::dropIfExists('user_balance_hists');
    }
}
