<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class NaviPayment extends Model
{
    protected $table = 'navi_payments';

    protected $fillable = [
        'start_date',
        'end_date',
        'description',
        'navi_quote',
        'btc_quote',
        'usd_quote',
        'total',
        'amount_btc',
        'amount_usd',
        'status'
    ];

    protected $hidden = ['description'];

    protected $appends = [
        'startLocal',
        'endLocal',
        'createdLocal',
        'decodedDescription'
    ];

    public function getStartLocalAttribute()
    {
        return Carbon::parse($this->start_date)->format('d/m/Y');
    }

    public function getEndLocalAttribute()
    {
        return Carbon::parse($this->end_date)->format('d/m/Y');
    }

    public function getCreatedLocalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y H:i');
    }

    public function getDecodedDescriptionAttribute()
    {
        return json_decode($this->description, true);
    }
}
