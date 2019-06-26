<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionHistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_hists', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('transaction_id')->unsigned();
            $table->uuid('user_id');
            $table->integer('coin_id')->unsigned();
            $table->uuid('wallet_id');
            $table->string('toAddress')->nullable();
            $table->decimal('amount', 18, 8);
            $table->decimal('fee', 18, 8)->default(0);
            $table->tinyInteger('status');
            $table->tinyInteger('type');
            $table->tinyInteger('category');
            $table->string('tx', 255)->nullable();
            $table->integer('confirmation')->default(0);
            $table->text('info')->nullable();
            $table->text('error')->nullable();

            $table->uuid('sender_user_id')->nullable();
            $table->boolean('is_gateway_payment')->default(0);

            $table->integer('system_account_id')->unsigned()->nullable();
            $table->uuid('user_account_id')->nullable();
            $table->text('file_path')->nullable();
            $table->decimal('tax', 18, 8)->default(0);

            $table->decimal('price', 18, 8)->nullable();
            $table->decimal('market', 18, 8)->nullable();

            $table->date('payment_at')->nullable();
            $table->dateTime('transaction_created_at')->nullable();
            $table->dateTime('transaction_updated_at')->nullable();

            $table->uuid('creator_user_id');

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
        Schema::dropIfExists('transaction_hists');
    }
}
