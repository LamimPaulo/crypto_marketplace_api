<?php

namespace App\Models;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Models\System\SystemAccount;
use App\Models\User\UserAccount;
use App\Models\User\UserWallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed user_id
 * @property mixed coin_id
 * @property mixed wallet_id
 * @property mixed toAddress
 * @property mixed amount
 * @property mixed fee
 * @property mixed status
 * @property mixed type
 * @property mixed category
 * @property mixed tx
 * @property mixed confirmation
 * @property mixed info
 * @property mixed error
 * @property mixed sender_user_id
 * @property mixed is_gateway_payment
 * @property mixed system_account_id
 * @property mixed user_account_id
 * @property mixed file_path
 * @property mixed tax
 * @property mixed price
 * @property mixed market
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed coin
 */
class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'coin_id',
        'wallet_id',
        'toAddress',
        'amount',
        'fee',
        'status',
        'type',
        'category',
        'tx',
        'confirmation',
        'info',
        'error',
        'sender_user_id',
        'is_gateway_payment',
        'system_account_id',
        'user_account_id',
        'file_path',
        'tax',
        'price',
        'market',
        'payment_at',
        'vout',
        'product_id'
    ];

    protected $hidden = [
        'user_id',
        'coin_id',
        'wallet_id',
        'sender_user_id',
        'system_account_id',
        'user_account_id',
        'file_path',
    ];

    protected $appends = [
        'categoryName',
        'typeName',
        'statusName',
        'statusClient',
        'statusClass',
        'dateLocal',
        'updatedLocal',
        'paymentDateLocal',
        'amountRounded',
        'feeRounded',
        'taxRounded',
        'marketRounded',
        'priceRounded',
        'timestamp',
        'file',
        'fileExt',
        'totalRounded',
        'total',
    ];

    public function getCategoryNameAttribute()
    {
        return EnumTransactionCategory::TYPES[$this->category];
    }

    public function getTypeNameAttribute()
    {
        return EnumTransactionType::TYPES[$this->type];
    }

    public function getStatusNameAttribute()
    {
        return EnumTransactionsStatus::STATUS[$this->status];
    }

    public function getStatusClientAttribute()
    {
        return EnumTransactionsStatus::STATUS_CLIENT[$this->status];
    }

    public function getStatusClassAttribute()
    {
        return EnumTransactionsStatus::STATUS_CLASS[$this->status];
    }

    public function getDateLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getPaymentDateLocalAttribute()
    {
        return !is_null($this->payment_at) ? Carbon::parse($this->payment_at)->format('d/m/Y') : '-';
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }

    public function getFileAttribute()
    {
        $url = '';
        if (Storage::disk('s3')->has($this->file_path)) {
            $url = Storage::disk('s3')->temporaryUrl($this->file_path, Carbon::now()->addMinutes(10));
        }
        return $url;
    }

    public function getFileExtAttribute()
    {
        $ext = '';
        if (!is_null($this->file_path) && !empty($this->file_path)) {
            $file = explode(".", $this->file_path);
            $ext = $file[1];
        }
        return $ext;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user_account()
    {
        return $this->belongsTo(UserAccount::class, 'user_account_id')->withTrashed();
    }

    public function system_account()
    {
        return $this->belongsTo(SystemAccount::class, 'system_account_id');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function wallet()
    {
        return $this->belongsTo(UserWallet::class, 'wallet_id');
    }

    public function internalWallet()
    {
        return $this->belongsTo(UserWallet::class, 'toAddress', 'address');
    }

    public function taxCoin()
    {
        return $this->hasMany(TaxCoinTransaction::class, 'operation_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'transaction_id');
    }

    public static function confirmation()
    {
        return self::join('coins', 'coins.id', '=', 'transactions.coin_id')
            ->whereIn('transactions.status', [EnumTransactionsStatus::PROCESSING, EnumTransactionsStatus::PENDING])
            ->where('transactions.type', '=', EnumTransactionType::IN)
            ->where('transactions.category', '=', EnumTransactionCategory::TRANSACTION)
            ->select('transactions.*', 'coins.abbr')
            ->get();
    }

    public static function getValueByDayUser($coin_id, $category, $is_internal = 0)
    {
        return self::where('user_id', '=', auth()->user()->id)
            ->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')
            ->where('created_at', '<=', date('Y-m-d') . ' 23:59:59')
            ->where('type', '=', EnumTransactionType::OUT)
            ->where('coin_id', '=', $coin_id)
            ->where('category', '=', $category)
            ->where('is_internal', $is_internal)->get();
    }

    public static function showByHash($hash)
    {
        return self::where('tx', '=', $hash)->first();
    }

    public static function listPending()
    {
        return self::join('user_wallets', 'user_wallets.id', '=', 'transactions.wallet_id')
            ->whereIn('transactions.status', [EnumTransactionsStatus::PENDING, EnumTransactionsStatus::ERROR])
            ->where('transactions.type', '=', EnumTransactionType::OUT)
            ->where('transactions.category', '=', EnumTransactionCategory::TRANSACTION)
            ->select(DB::raw('transactions.*, user_wallets.address'))
            ->get();
    }

    public static function listAuthorized($coin_id)
    {
        return self::where([
            'status' => EnumTransactionsStatus::AUTHORIZED,
            'type' => EnumTransactionType::OUT,
            'category' => EnumTransactionCategory::TRANSACTION,
            'coin_id' => $coin_id
        ])->with(['wallet', 'user'])
            ->groupBy('toAddress')
            ->orderBy('amount')
            ->take(50);
    }

    public static function listUnique($id)
    {
        return self::join('user_wallets', 'user_wallets.id', '=', 'transactions.wallet_id')
            ->where('transactions.status', '=', EnumTransactionsStatus::ABOVELIMIT)
            ->where('transactions.type', '=', EnumTransactionType::OUT)
            ->where('transactions.category', '=', EnumTransactionCategory::TRANSACTION)
            ->where('transactions.id', '=', $id)
            ->select(DB::raw('transactions.*, user_wallets.address'))
            ->first();
    }

    public function getTotalAttribute()
    {
        return $this->amount + $this->fee + $this->tax;
    }

    public function getTotalRoundedAttribute()
    {
        return sprintf("%.{$this->coin->decimal}f", $this->total);
    }

    public function getAmountRoundedAttribute()
    {
        return sprintf("%.{$this->coin->decimal}f", $this->amount);
    }

    public function getFeeRoundedAttribute()
    {
        return sprintf("%.{$this->coin->decimal}f", $this->fee);
    }

    public function getTaxRoundedAttribute()
    {
        return sprintf("%.{$this->coin->decimal}f", $this->tax);
    }

    public function getMarketRoundedAttribute()
    {
        return sprintf("%.{$this->coin->decimal}f", $this->market);
    }

    public function getPriceRoundedAttribute()
    {
        return sprintf("%.{$this->coin->decimal}f", $this->price);
    }

    public function getTimestampAttribute()
    {
        return Carbon::now()->format('H:i:s');
    }

    /*
        'amount',
        'fee',
        'tax',
    */
    public function getAmountAttribute($value)
    {
        return sprintf("%.8f", $value);
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = sprintf("%.8f", $value);
    }

    public function getFeeAttribute($value)
    {
        return sprintf("%.8f", $value);
    }

    public function setFeeAttribute($value)
    {
        $this->attributes['fee'] = sprintf("%.8f", $value);
    }

    public function getTaxAttribute($value)
    {
        return sprintf("%.8f", $value);
    }

    public function setTaxAttribute($value)
    {
        $this->attributes['tax'] = sprintf("%.8f", $value);
    }

}
