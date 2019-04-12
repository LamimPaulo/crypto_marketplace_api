<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentType extends Model
{
    use SoftDeletes;

    protected $fillable = ['type'];

    public function documents()
    {
        return $this->hasMany(Document::class, 'document_type_id');
    }
}
