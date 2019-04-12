<?php

namespace App\Models;

use App\Enum\EnumCalcType;
use App\Enum\EnumOperations;
use App\Enum\EnumTaxType;
use App\Models\User\UserLevel;
use Illuminate\Database\Eloquent\Model;

class TaxCoin extends Model
{
    protected $fillable = [
        'coin_id',
        'user_level_id',
        'coin_tax_type',
        'description',
        'value',
        'operation',
        'calc_type'
    ];

    protected $appends = ['taxTypeName', 'calcTypeName', 'operationName'];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function level()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id');
    }

    public function getTaxTypeNameAttribute()
    {
        return EnumTaxType::OPERATIONS[$this->coin_tax_type];
    }

    public function getCalcTypeNameAttribute()
    {
        return EnumCalcType::TYPE[$this->calc_type];
    }

    public function getOperationNameAttribute()
    {
        return EnumOperations::OPERATIONS[$this->operation];
    }

    public static function sum($user_level, $coin_id)
    {
        return self::where('user_level_id', '=', $user_level)
            ->where('coin_id', '=', $coin_id)
            ->sum('value');
    }

    /**
     *
     * @param int $coins_id
     * @param int $operation
     * @param int $user_level
     * @return type
     */
    public static function getByOperationAndUserLevel(int $coins_id, int $operation, int $user_level)
    {
        return self::where('operation', '=', $operation)
            ->where('coin_id', '=', $coins_id)
            ->where('user_level_id', '=', $user_level)
            ->get();
    }


    /**
     *
     * @param int $coins_id
     * @param int $operation
     * @param int $user_level
     * @return type
     */
    public static function getByOperationAndUserLevelEmpresa(int $coins_id, int $operation, int $user_level)
    {
        return self::where('operation', '=', $operation)
            ->where('coin_id', '=', $coins_id)
            ->where('user_level_id', '=', $user_level)
            ->whereIn('coin_tax_type', [EnumTaxType::TED, EnumTaxType::OPERACAO])
            ->get();
    }
}
