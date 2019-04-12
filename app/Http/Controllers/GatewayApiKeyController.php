<?php

namespace App\Http\Controllers;

use App\Enum\EnumGatewayCategory;
use App\Enum\EnumGatewayStatus;
use App\Enum\EnumGatewayType;
use App\Enum\EnumOperationType;
use App\Enum\EnumUserWalletType;
use App\Http\Requests\GatewayApiKeyRequest;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Gateway;
use App\Models\GatewayApiKey;
use App\Models\GatewayStatus;
use App\Models\SysConfig;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use App\Services\TaxCoinService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class GatewayApiKeyController extends Controller
{
    protected $balanceService;
    protected $conversorService;
    protected $taxCoinService;

    public function __construct(
        BalanceService $balance,
        ConversorService $conversor,
        TaxCoinService $taxCoin)
    {
        $this->conversorService = $conversor;
        $this->taxCoinService = $taxCoin;
        $this->balanceService = $balance;
    }

    public function index()
    {
        try {
            $keys = GatewayApiKey::where('user_id', auth()->user()->id);
            return response([
                'status' => 'success',
                'key' => $keys->first(),
                'count' => $keys->count(),
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(GatewayApiKeyRequest $request)
    {
        try {
            $gatewayKey = GatewayApiKey::where('user_id', auth()->user()->id)->first();
            if ($gatewayKey) {
                throw new \Exception(trans('messages.auth.already_have_active_key'));
            }

            $newKey = GatewayApiKey::firstOrNew(['user_id' => auth()->user()->id])->makeVisible('secret');
            $newKey->api_key = Uuid::uuid4()->toString();
            $newKey->secret = str_replace("-", "", Uuid::uuid4()->toString());
            $newKey->ip = $request->ip ?? '%';
            $newKey->payment_coin = $request->payment_coin;
            $newKey->save();

            return response([
                'status' => 'success',
                'key' => $newKey,
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(GatewayApiKeyRequest $request)
    {
        try {
            $newKey = GatewayApiKey::where(['user_id' => auth()->user()->id])->firstOrFail();
            $newKey->api_key = Uuid::uuid4()->toString();
            $newKey->secret = str_replace("-", "", Uuid::uuid4()->toString());
            $newKey->ip = $request->ip ?? '%';
            $newKey->payment_coin = $request->payment_coin;
            $newKey->save();
            $newKey->makeVisible('secret');

            return response([
                'status' => 'success',
                'key' => $newKey,
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function payment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        try {

            $gatewayKey = GatewayApiKey::where('user_id', auth()->user()->id)->first();
            if (!$gatewayKey) {
                throw new \Exception(trans('gateway.must_create_api_key'));
            }

            $amount = $request->amount;

            $fiatCoin = UserWallet::whereHas(
                'coin', function ($coin) {
                return $coin->where('is_crypto', false);
            })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => auth()->user()->id])->first()->coin_id;

            $time = SysConfig::first()->time_gateway;

            $tx = Uuid::uuid4()->toString();

            $gateway = Gateway::create([
                'user_id' => auth()->user()->id,
                'fiat_coin_id' => $fiatCoin,
                'fiat_amount' => $amount,
                'tx' => $tx,
                'status' => EnumGatewayStatus::NEWW,
                'type' => EnumGatewayType::PAYMENT,
                'tax' => 0,
                'category' => EnumGatewayCategory::PAYMENT,
                'time_limit' => Carbon::now()->addMinutes($time)
            ]);

            GatewayStatus::create([
                'status' => $gateway->status,
                'gateway_id' => $gateway->id
            ]);

            return response([
                'status' => 'success',
                'payment' => $gateway,
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function estimatePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $amount = $request->amount;

        $wallets = UserWallet::whereHas(
            'coin', function ($coin) {
            return $coin->where('is_crypto', true);
        })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => auth()->user()->id])->orderBy('coin_id')->get();

        $fiat = UserWallet::whereHas(
            'coin', function ($coin) {
            return $coin->where('is_crypto', false);
        })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => auth()->user()->id])->first();

        $coins = [];
        foreach ($wallets as $k => $wallet) {
            $quote = CoinQuote::where(['coin_id' => $wallet->coin_id, 'quote_coin_id' => $fiat->coin_id])->first()->sell_quote;
            $coins[$k] = [
                'coin_name' => $wallet->coin->name,
                'coin_abbr' => $wallet->coin->abbr,
                'amount' => $amount / $quote
            ];
        }

        return $coins;
    }

    public function listPayments()
    {
        try {
            $transactions = Gateway::with('coin', 'fiat_coin')
                ->where('user_id', auth()->user()->id)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            return response($transactions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'payments' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function showPayment($tx)
    {
        try {
            $payment = Gateway::with('coin')->where('tx', $tx)->firstOrFail();

            if (!Carbon::parse($payment->time_limit)->gte(Carbon::now())) {
                throw new \Exception(trans('messages.gateway.payment_time_expired'));
            }

            $user_id = $payment->user->id;

            $wallets = UserWallet::whereHas(
                'coin', function ($coin) {
                return $coin->where('is_crypto', true);
            })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => $user_id])->orderBy('coin_id')->get();

            $fiat = UserWallet::whereHas(
                'coin', function ($coin) {
                return $coin->where('is_crypto', false);
            })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => $user_id])->first();

            $coins = [];
            foreach ($wallets as $k => $wallet) {
                $quote = CoinQuote::where(['coin_id' => $wallet->coin_id, 'quote_coin_id' => $fiat->coin_id])->first()->sell_quote;
                $coins[$k] = [
                    'coin_name' => $wallet->coin->name,
                    'coin_abbr' => $wallet->coin->abbr,
                    'icon' => $wallet->coin->icon,
                    'amount' => $payment->fiat_amount / $quote
                ];
            }
            unset($payment->user);
            return response([
                'message' => 'success',
                'payment' => $payment,
                'coins' => $coins
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage() . " ({$ex->getLine()})"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updatePayment(Request $request)
    {

        $request->validate([
            'coin' => 'required|exists:coins,abbr',
            'tx' => 'required|exists:gateway,tx'
        ], [
            'coin.required' => trans('messages.general.invalid_data'),
            'tx.required' => trans('messages.general.invalid_data'),
            'coin.exists' => trans('messages.general.invalid_data'),
            'tx.exists' => trans('messages.general.invalid_data')
        ]);

        try {
            DB::beginTransaction();

            $payment = Gateway::with('coin')->where('tx', $request->tx)->firstOrFail();

            if ($payment->status != EnumGatewayStatus::NEWW) {
                throw new \Exception(trans('messages.gateway.payment_could_not_be_updated'));
            }

            if (!Carbon::parse($payment->time_limit)->gte(Carbon::now())) {
                throw new \Exception(trans('messages.gateway.payment_time_expired'));
            }

            $coin = Coin::where('abbr', $request->coin)->firstOrFail()->id;
            $fiat = UserWallet::whereHas(
                'coin', function ($coin) {
                return $coin->where('is_crypto', false);
            })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => $payment->user->id])->first();

            $quote = CoinQuote::where(['coin_id' => $coin, 'quote_coin_id' => $fiat->coin->id])->first()->sell_quote;

            $payment->update([
                'value' => $quote,
                'coin_id' => $coin,
                'amount' => $payment->fiat_amount / $quote,
                'address' => env('APP_ENV') == 'local' ? Uuid::uuid4()->toString()
                    : OffScreenController::post(EnumOperationType::CREATE_ADDRESS, NULL, $coin->abbr)
            ]);

            DB::commit();

            return response([
                'message' => trans('messages.gateway.address_generated'),
                'payment' => Gateway::with('coin')->where('tx', $request->tx)->firstOrFail()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }
}
