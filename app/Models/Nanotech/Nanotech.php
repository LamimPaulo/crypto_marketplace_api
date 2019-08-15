<?php

namespace App\Models\Nanotech;

use App\Models\Coin;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Nanotech extends Model
{
    protected $table = 'nanotech';

    protected $fillable = [
        'user_id',
        'coin_id',
        'type_id',
        'amount',
        'status'
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function type()
    {
        return $this->belongsTo(NanotechType::class, 'type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operations()
    {
        $this->hasMany(NanotechOperation::class, 'investment_id');
    }

    public static function increments($operation)
    {
        $nanotech = Nanotech::where([
            'user_id' => $operation->user_id,
            'type_id' => $operation->type_id
        ]);

        $nanotech->increment('amount', (string)$operation->amount);
    }

}
