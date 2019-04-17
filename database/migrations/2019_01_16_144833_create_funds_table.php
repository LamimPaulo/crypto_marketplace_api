<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->decimal('buy_tax', 12, 2)->default(1);
            $table->decimal('redemption_tax', 12, 2)->default(0.5);
            $table->decimal('early_redemption_tax', 12, 2)->default(10);
            $table->integer('coin_id')->nullable()->default(2);
            $table->decimal('price', 18, 8)->default(0);
            $table->decimal('monthly_profit', 12, 2)->default(0);
            $table->integer('validity')->unsigned()->default(6);
            $table->boolean('is_active')->default(1);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('funds');
    }
}
