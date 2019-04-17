<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enum\EnumTransactionsStatus;

class CreateFundTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fund_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->integer('fund_id')->unsigned();
            $table->integer('coin_id')->unsigned();
            $table->integer('transaction_id')->unsigned()->nullable();
            $table->decimal('value', 18, 8);
            $table->decimal('tax', 18, 8)->default(0);
            $table->decimal('profit_percent', 5, 2)->default(0);
            $table->tinyInteger('type');//EnumTransactionType
            $table->tinyInteger('category');//EnumFundTransactionCategory
            $table->tinyInteger('status')->default(EnumTransactionsStatus::PENDING);
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
        Schema::dropIfExists('fund_transactions');
    }
}
