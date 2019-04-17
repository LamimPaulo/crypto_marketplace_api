<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Enum\EnumFundBalanceType;

class CreateFundBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fund_balances', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('user_id');
            $table->integer('fund_id')->unsigned();
            $table->decimal('balance', 28,18)->default(0);
            $table->enum('type', [EnumFundBalanceType::BLOCKED, EnumFundBalanceType::FREE])
                ->default(EnumFundBalanceType::BLOCKED);
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
        Schema::dropIfExists('fund_balances');
    }
}
