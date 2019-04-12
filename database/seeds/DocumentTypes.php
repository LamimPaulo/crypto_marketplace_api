<?php

use Illuminate\Database\Seeder;
use App\Models\User\DocumentType;

class DocumentTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentType::create([
            'id' => 1,
            'type' => 'CPF',
        ]);
        DocumentType::create([
            'id' => 2,
            'type' => 'Selfie',
        ]);
    }
}
