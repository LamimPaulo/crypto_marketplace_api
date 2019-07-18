<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLqxWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lqx_withdrawals', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->boolean('is_executed');
            $table->decimal('percent', 5, 2)->default(25);
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
        Schema::dropIfExists('lqx_withdrawals');
    }
}
