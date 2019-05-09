<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToUserActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE user_activity_log ADD CONSTRAINT fk_user_activity_subject_id FOREIGN KEY (subject_id) REFERENCES users(id);');
        DB::statement('DELETE FROM user_activity_log WHERE subject_id NOT IN (SELECT id FROM users)');
        DB::statement('ALTER TABLE user_activity_log ADD CONSTRAINT fk_user_activity_causer_id FOREIGN KEY (causer_id) REFERENCES users(id);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
