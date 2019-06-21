<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE documents ADD CONSTRAINT fk_documents_user_id FOREIGN KEY (user_id) REFERENCES users(id);');
        DB::statement('ALTER TABLE documents ADD CONSTRAINT fk_documents_document_type_id FOREIGN KEY (document_type_id) REFERENCES document_types(id);');
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
