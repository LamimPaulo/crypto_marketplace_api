<?php

namespace App\Models\Mining;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed created_at
 * @property mixed updated_at
 */
class MiningQuota extends Model
{
    protected $fillable = ['user_id', 'mining_plan_id', 'ths_quota', 'buy_price'];

    protected $appends = ['dateLocal', 'updatedLocal'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(MiningPlan::class, 'mining_plan_id');
    }

    public function getDateLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }
}
