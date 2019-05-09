<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->integer('bank_id')->unsigned()->nullable();
            $table->string('agency')->nullable();
            $table->string('account')->nullable();
            $table->string('agency_digit')->nullable();
            $table->string('account_digit')->nullable();
            $table->string('nickname');
            $table->text('email');
            $table->string('type')->default(1);
            $table->string('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_accounts');
    }
}
