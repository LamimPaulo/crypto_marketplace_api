<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToNanotechOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE nanotech_operations ADD CONSTRAINT fk_nanotech_operations_user_id FOREIGN KEY (user_id) REFERENCES users(id);');
        DB::statement('ALTER TABLE nanotech_operations ADD CONSTRAINT fk_nanotech_operations_investment_id FOREIGN KEY (investment_id) REFERENCES nanotech(id);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nanotech_operations', function (Blueprint $table) {
            //
        });
    }
}
