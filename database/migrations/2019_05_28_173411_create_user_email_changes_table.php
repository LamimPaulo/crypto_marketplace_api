<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserEmailChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_email_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('old_email');
            $table->string('new_email');
            $table->uuid('user_id');
            $table->uuid('creator_id');
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
        Schema::dropIfExists('user_email_changes');
    }
}
