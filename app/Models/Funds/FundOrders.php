<?php

namespace App\Models\Funds;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FundOrders extends Model
{
    protected $fillable = [
        'user_id',
        'fund_id',
        'side',
        'quotes',
        'quotes_executed',
        'admin_tax',
        'tax',
        'is_executed',
        'value'
    ];

    protected $appends = ['createdLocal'];

    public function fund()
    {
        return $this->belongsTo(Funds::class, 'fund_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }
}
