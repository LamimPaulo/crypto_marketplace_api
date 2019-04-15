<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Enum\EnumFundType;

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
            $table->enum('type', [EnumFundType::LIMITED, EnumFundType::UNLIMITED])->default(EnumFundType::UNLIMITED);
            $table->decimal('buy_tax', 18, 8)->default(1);
            $table->decimal('sell_tax', 18,8)->default(0.5);
            $table->decimal('admin_tax', 18,8)->default(2);
            $table->decimal('start_price', 18,8)->default(0);
            $table->decimal('value', 28,18)->default(0);
            $table->decimal('start_amount', 18,8)->default(0);
            $table->boolean('is_active')->default(1);
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
