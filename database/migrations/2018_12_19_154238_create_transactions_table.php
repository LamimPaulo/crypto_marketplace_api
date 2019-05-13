<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->integer('coin_id')->unsigned();
            $table->uuid('wallet_id');
            $table->string('toAddress')->nullable();
            $table->decimal('amount', 28, 18);
            $table->decimal('fee', 28,18)->default(0);
            $table->tinyInteger('status');
            $table->tinyInteger('type');
            $table->tinyInteger('category');
            $table->string('tx',255)->nullable();
            $table->integer('confirmation')->default(0);
            $table->text('info')->nullable();
            $table->text('error')->nullable();

            $table->uuid('sender_user_id')->nullable();
            $table->boolean('is_gateway_payment')->default(0);

            $table->integer('system_account_id')->unsigned()->nullable();
            $table->uuid('user_account_id')->nullable();
            $table->text('file_path')->nullable();
            $table->decimal('tax', 28,18)->default(0);

            $table->decimal('price', 28,18)->nullable();
            $table->decimal('market', 28,18)->nullable();

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
        Schema::dropIfExists('transactions');
    }
}
