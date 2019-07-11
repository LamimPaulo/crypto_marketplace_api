<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Enum\EnumUserLevelLimitType;

class CreateUserLevelLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_level_limits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_level_id')->unsigned();
            $table->integer('coin_id')->unsigned();
            $table->tinyInteger('type')->default(EnumUserLevelLimitType::EXTERNAL);
            $table->decimal('limit', 18, 8);
            $table->decimal('limit_auto', 18, 8);
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
        Schema::dropIfExists('user_level_limits');
    }
}
