<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_id')->unsigned()->nullable();
            $table->string('agency')->nullable();
            $table->string('account')->nullable();
            $table->string('agency_digit')->nullable();
            $table->string('account_digit')->nullable();
            $table->string('name');
            $table->string('document');
            $table->text('email')->nullable();
            $table->string('type')->default(1);
            $table->string('observation')->nullable();
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
        Schema::dropIfExists('system_accounts');
    }
}
