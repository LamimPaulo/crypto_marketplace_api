<?php

namespace App;

use App\Models\Country;
use App\Models\Gateway;
use App\Models\GatewayApiKey;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\User\Document;
use App\Models\User\UserLevel;
use App\Models\User\UserWallet;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Ramsey\Uuid\Uuid;

/**
 * @property mixed birthdate
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens;

    public $incrementing = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $uuid4 = Uuid::uuid4();
            $instance->id = $uuid4->toString();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'email_verified_at',
        'pin',
        'pin_filled',
        'is_admin',
        'phone',
        'document',
        'document_verified',
        'mothers_name',
        'gender',
        'country_id',
        'phone_verified_at',
        'birthdate',
        'user_level_id',
        'google2fa_secret',
        'is_google2fa_active',
        'is_under_analysis',
        'zip_code',
        'state',
        'city',
        'district',
        'address',
        'number',
        'complement',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'id', 'pin', 'is_admin', 'google2fa_secret'
    ];

    protected $appends = ['createdLocal', 'time'];

    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getTimeAttribute()
    {
        return Carbon::now()->toIso8601ZuluString();
    }

    public function verifyUser()
    {
        return $this->hasOne(VerifyUser::class, 'user_id');
    }

    public function getBirthdateAttribute($v)
    {
        return Carbon::parse($v)->format('d/m/Y');
    }

    public function getNameAttribute($v)
    {
        return $v ?? $this->username;
    }

    public function level()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function api_key()
    {
        return $this->hasOne(GatewayApiKey::class, 'user_id');
    }

    public function investments()
    {
        return $this->hasMany(Nanotech::class, 'user_id');
    }

    public function investment_operations()
    {
        return $this->hasMany(NanotechOperation::class, 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'user_id');
    }

    public function gateway_payments()
    {
        return $this->hasMany(Gateway::class, 'user_id');
    }

    public function wallets()
    {
        return $this->hasMany(UserWallet::class, 'user_id');
    }
}
