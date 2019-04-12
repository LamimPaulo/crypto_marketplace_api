<?php

namespace App\Http\Controllers;

use App\Enum\EnumUserWalletType;
use App\Http\Requests\PharaosGatewayApiKeyRequest;
use App\Http\Requests\PharaosGatewayConvertRequest;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\PharaosGatewayApiKey;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class PharaosGatewayApiKeyController extends Controller
{
    protected $balanceService;
    protected $conversorService;

    public function __construct(
        BalanceService $balance,
        ConversorService $conversor)
    {
        $this->conversorService = $conversor;
        $this->balanceService = $balance;
    }

    public function index()
    {
        try {
            $keys = PharaosGatewayApiKey::where('user_id', auth()->user()->id);
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

    public function store(PharaosGatewayApiKeyRequest $request)
    {
        try {
            $gatewayKey = PharaosGatewayApiKey::where('user_id', auth()->user()->id)->first();
            if ($gatewayKey) {
                throw new \Exception(trans('messages.auth.already_have_active_key'));
            }

            $newKey = PharaosGatewayApiKey::firstOrNew(['user_id' => auth()->user()->id])->makeVisible('secret');
            $newKey->api_key = Uuid::uuid4()->toString();
            $newKey->secret = str_replace("-", "", Uuid::uuid4()->toString());
            $newKey->ip = $request->ip;
            $newKey->type = $request->type;
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

    public function update(PharaosGatewayApiKeyRequest $request)
    {
        try {
            $newKey = PharaosGatewayApiKey::where(['user_id' => auth()->user()->id])->firstOrFail();
            $newKey->api_key = Uuid::uuid4()->toString();
            $newKey->secret = str_replace("-", "", Uuid::uuid4()->toString());
            $newKey->ip = $request->ip;
            $newKey->type = $request->type;
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

    public function balances(Request $request)
    {
        try {
            $wallets = UserWallet::whereHas(
                'coin', function ($coin) {
                return $coin->where('is_crypto', true);
            })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => $request->user->id])->orderBy('coin_id')->get();

            $fiat = UserWallet::whereHas(
                'coin', function ($coin) {
                return $coin->where('is_crypto', false);
            })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => $request->user->id])->first();

            $coins = [];
            foreach ($wallets as $k => $wallet) {
                $quote = CoinQuote::where(['coin_id' => $wallet->coin_id, 'quote_coin_id' => $fiat->coin_id])->first()->sell_quote;
                $coins[$k] = [
                    'coin_name' => $wallet->coin->name,
                    'coin_abbr' => $wallet->coin->abbr,
                    'balance' => $wallet->balance,
                    'fiat_coin_abbr' => $fiat->coin->abbr,
                    'fiat_balance' => $wallet->balance * $quote
                ];
            }

            return response([
                'message' => trans('messages.general.success'),
                'balances' => $coins
                ], Response::HTTP_OK);

        } catch (\Exception $e) {

        }

    }

    public function convert(PharaosGatewayConvertRequest $request)
    {
        $amount = (float)$request->amount;
        $coin_id = Coin::getByAbbr($request->coin)->id;
        $fiat = UserWallet::whereHas(
            'coin', function ($coin) {
            return $coin->where('is_crypto', false);
        })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => $request->user->id])->first();

        $quote = CoinQuote::where(['coin_id' => $coin_id, 'quote_coin_id' => $fiat->coin_id])->first()->sell_quote;
        return [
            'amount_request' => $amount,
            'coin_wallet' => $fiat->coin->abbr,
            'amount_result' => $amount / $quote,
            'fiat_wallet' => $fiat->coin->abbr,
            'has_balance' => $this->balanceService->verifyBalance($amount, $request->coin, EnumUserWalletType::WALLET, $request->user->id),
        ];

    }
}
