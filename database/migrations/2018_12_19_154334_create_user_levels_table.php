<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('name');
            $table->decimal('limit_btc_diary', 28, 18);
            $table->decimal('limit_brl_diary', 28, 18);
            $table->decimal('limit_transaction_auto', 28, 18);
            $table->decimal('brokerage_fee', 6, 3);
            $table->boolean('is_referrable')->default(0);
            $table->decimal('referral_profit', 6, 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('user_level_id')->unsigned()->default(1);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_levels');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_level_id');
        });
    }
}
