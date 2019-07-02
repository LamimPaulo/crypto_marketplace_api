<?php

namespace App;

use App\Models\Country;
use App\Models\Gateway;
use App\Models\GatewayApiKey;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\SysConfig;
use App\Models\System\ActivityLogger;
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

    private $default_time = -3;

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
        'password', 'remember_token', 'id', 'pin', 'google2fa_secret'
    ];

    protected $appends = ['createdLocal', 'time', 'timezoneSettings'];

    public function getNameAttribute($value)
    {
        $name = $value ?? $this->username;

        if ($this->is_admin) {
            return explode(" ", $name)[0];
        }
        return $name;
    }

    public function getTimezoneSettingsAttribute()
    {
        $config = SysConfig::first();
        $country = Country::find($this->country_id);
        $days = explode(',', $config->withdrawal_days);

        $now = strtotime(Carbon::now()->addHours($this->default_time));

        $min_hour = strtotime($config->min_withdrawal_hour);
        $max_hour = strtotime($config->max_withdrawal_hour);

        return [
            'min_withdrawal_hour' => Carbon::parse($config->min_withdrawal_hour)->addHours($country->timezone)->format("H:i"),
            'max_withdrawal_hour' => Carbon::parse($config->max_withdrawal_hour)->addHours($country->timezone)->format("H:i"),
            'withdrawal_days' => $config->withdrawalDaysStr,

            'withdrawal_time' => ($now >= $min_hour AND $now <= $max_hour) ? 1 : 0,
            'withdrawal_day' => in_array(date('w'), $days) ? 1 : 0,
        ];
    }

    public function getCreatedLocalAttribute()
    {
        if ($this->created_at) {
            return $this->created_at->format('d/m/Y H:i');
        }
        return Carbon::now()->format('d/m/Y H:i');
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

    public function level()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function gateway_key()
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

    public function activities()
    {
        return $this->hasMany(ActivityLogger::class, 'subject_id');
    }

    public function causer()
    {
        return $this->hasMany(ActivityLogger::class, 'causer_id');
    }

    public function user_role()
    {
        return $this->hasOne(UserRole::class, 'user_id');
    }
}
