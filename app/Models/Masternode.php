<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Masternode extends Model
{
    //
    protected $fillable = [
        'coin_id',
        'roi',
        'daily_return',
        'daily_return_btc'
    ];

    protected $appends = ['createdLocal', 'roiLocal'];

    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getRoiLocalAttribute()
    {
        return sprintf("%.3f", $this->roi);
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function hists()
    {
        return $this->hasMany(MasternodeHist::class, 'coin_id', 'coin_id');
    }

}
