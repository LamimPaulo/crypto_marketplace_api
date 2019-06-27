<?php

namespace App\Http\Controllers;

use App\Enum\EnumGatewayCategory;
use App\Enum\EnumGatewayPaymentCoin;
use App\Enum\EnumGatewayStatus;
use App\Enum\EnumGatewayType;
use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Models\Coin;
use App\Models\Gateway;
use App\Models\GatewayApiKey;
use App\Models\GatewayStatus;
use App\Models\SysConfig;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use App\Services\GatewayService;
use App\Services\TaxCoinService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class GatewayController extends Controller
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

    public function checkKey(Request $request)
    {
        $request->validate([
            'api_key' => 'required'
        ]);

        try {
            $key = GatewayApiKey::where('api_key', $request->get('api_key'))->exists();

            if (!$key) {
                throw new \Exception(trans('messages.auth.invalid_key'));
            }

            return response([
                'status' => 'success',
                'message' => trans('messages.auth.valid_key')
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_OK);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'abbr' => 'required|exists:coins,abbr'
        ]);

        try {
            DB::beginTransaction();

            $amount = floatval($request->amount);

            $fiatCoin = UserWallet::whereHas(
                'coin', function ($coin) {
                return $coin->where('is_crypto', false);
            })->where(['type' => EnumUserWalletType::WALLET, 'user_id' => $request->user->id])->first();

            if ($fiatCoin->coin->abbr === $request->abbr) {
                throw new \Exception("A moeda requisitada é inválida.");
            }

            $time = env('GATEWAY_POS_TIME') ?? 10;
            $tx = Uuid::uuid4()->toString();

            //criar address na moeda necessária
            $quote = $this->conversorService::FIAT2CRYPTO_MAX($amount, $request->abbr, $fiatCoin->coin->abbr);

            $gateway = Gateway::create([
                'gateway_api_key_id' => $request->gateway_api_key_id,
                'coin_id' => Coin::getByAbbr($request->abbr)->id,
                'address' => $this->newAddress($request->abbr),
                'amount' => $quote['amount'],
                'value' => $quote['quote'],

                'user_id' => $request->user->id,
                'fiat_coin_id' => $fiatCoin->coin->id,
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

            DB::commit();

            return response([
                'status' => 'success',
                'payment' => $gateway->address,
                'amount' => $gateway->amount,
                'coin' => $gateway->coin->abbr,
                'coin_name' => $gateway->coin->name,
                'fiat_amount' => $gateway->fiat_amount,
                'fiat' => number_format($gateway->fiat_amount, 2, ',', '.'),
                'qr_code' => strtolower(Coin::getByAbbr($request->abbr)->name) . ':' . $gateway->address . '?amount=' . $gateway->amount
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    protected function newAddress($abbr)
    {
        try {
            $address = env('APP_ENV') == 'local' ? Uuid::uuid4()->toString() : OffScreenController::post(EnumOperationType::CREATE_ADDRESS, null, $abbr);
            return $address;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function show($address)
    {
        return Gateway::where('address', $address)->first();
    }

    public function status($payment)
    {
        try {
            $gateway = Gateway::where('address', $payment)->orWhere('tx', $payment)->first();

            if (!isset($gateway->status)) {
                return response(['message' => trans('messages.gateway.payment_not_found')], Response::HTTP_NOT_FOUND);
            }

            return response([
                "status_name" => EnumGatewayStatus::SITUATION[$gateway->status],
                "status" => $gateway->status,
                "payment" => $gateway->address,
                "tx" => $gateway->tx,
                "created" => $gateway->created_at,
                "confirmations" => $gateway->confirmations,
                "amount" => sprintf('%.8f', floatval($gateway->amount)),
                "coin" => $gateway->coin->abbr,
                "coin_name" => $gateway->coin->name,
                "fiat_amount" => $gateway->fiat_amount,
                "fiat" => number_format($gateway->fiat_amount, 2, ',', '.'),
                "fiat_abbr" => $gateway->fiat_coin->abbr
            ]);
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public static function update($transaction)
    {
        try {
            $gateway = self::show($transaction['toAddress']);
            if ($gateway->status === EnumGatewayStatus::NEWW) {
                $amount = abs($transaction['amount']);
                $status = self::setStatus($gateway, $amount);

                $gateway->update([
                    'status' => $status,
                    'txid' => $transaction['txid'],
                    'received' => $transaction['amount'],
                    'confirmations' => 1
                ]);

                GatewayStatus::create([
                    'status' => $gateway->status,
                    'gateway_id' => $gateway->id
                ]);

                return trans('messages.general.status_updated');
            }
            return false;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     *
     * @param mixed $transaction
     * @param mixed $expected
     * @return int
     */
    private static function setStatus($transaction, $expected)
    {
        $transaction->amount = floatval($transaction->amount);
        $expected = floatval($expected);

        if ($transaction->amount == $expected) {
            return EnumGatewayStatus::DONE;
        } else if ($transaction->amount < $expected) {
            return EnumGatewayStatus::UNDERPAID;
        } else if ($transaction->amount > $expected) {
            return EnumGatewayStatus::OVERPAID;
        }
        return $transaction->status;
    }

    /**
     * Recupera o pagamento de fatura
     *
     * @param int $payment
     * @return type
     */
    public function showPayment($payment)
    {
        try {
            $gateway = Gateway::where('tx', $payment)
                ->orWhere('address', $payment)
                ->first();


            if (!is_null($gateway)) {
                $total = ($gateway->amount + $gateway->taxas);
                $histStatus = $gateway->histStatus;
                $coin = $gateway->coin;
                $total = sprintf('%.' . $coin->decimal . 'f', floatval($total));

                foreach ($histStatus as $key => $value) {
                    $histStatus[$key]->name = EnumGatewayStatus::SITUATION[$histStatus[$key]->status];
                }

                //$created = Carbon::parse($gateway->created_at)->addMinutes(10);
                $created = Carbon::parse($gateway->created_at);
                $current = Carbon::now();
                $diff = $current->diffInMinutes($created);

                if ($current->lessThanOrEqualTo($created)) {
                    return response([
                        'total' => $total,
                        'address' => $gateway->address,
                        'created' => $created->format('M d, Y H:i:s'),
                        'expired' => $diff,
                        'hist_status' => $gateway->histStatus
                    ], Response::HTTP_OK);
                }

                return response([
                    'eita' => '',
                    'total' => $total,
                    'address' => $gateway->address,
                    'created' => $created->format('M d, Y H:i:s'),
                    'expired' => $diff,
                    'hist_status' => $gateway->histStatus
                ], Response::HTTP_OK);
            }
            return response([
                'message' => trans('messages.gateway.payment_not_found'),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Recupera o saque
     *
     * @param int $payment
     * @return response
     */
    public function withdraw($payment)
    {
        try {
            $gateway = Gateway::where('tx', '=', $payment)
                ->where('type', '=', EnumTransactionType::OUT)
                ->firstOrFail();


            $coin = Coin::find($gateway->coin_id);

            $total = ($gateway->amount + $gateway->tax);
            $histStatus = $gateway->histStatus;
            $coin = $gateway->coin;

            $total = sprintf('%.' . $coin->decimal . 'f', floatval($total));
            foreach ($histStatus as $key => $value) {
                $histStatus[$key]->name = EnumGatewayStatus::SITUATION[$histStatus[$key]->status];
            }

            return response([
                'total' => $total,
                'address' => $gateway->address,
                'hist_status' => $gateway->histStatus
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'message' => 'Depósito não encontrado'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Recupera as transações de saque que ainda não tem o numero de confirmações necessarias
     *
     * @return GatewayController
     */
    public static function confirmation()
    {
        $transactions = Gateway::with([
            'user' => function ($user) {
                return $user->with(['gateway_key', 'level']);
            },])
            ->where('type', '=', EnumTransactionType::IN)
            ->whereRaw('LENGTH(txid) > 36')
            ->whereIn('status', [EnumGatewayStatus::DONE, EnumGatewayStatus::OVERPAID, EnumGatewayStatus::UNDERPAID])
            ->get()->makeVisible(['coin_id', 'status', 'user_id']);

        foreach ($transactions as $transaction) {
            return self::_confirmations($transaction);
        }
    }

    /**
     * Analisa o número de confirmações de um transação
     *
     * @param mixed $transaction
     * @return GatewayController|string
     */
    private static function _confirmations($transaction)
    {
        try {
            DB::beginTransaction();
            $abbr = Coin::find($transaction->coin_id)->abbr;

            $result = OffScreenController::post(EnumOperationType::CONFIRMATION, ['txid' => $transaction->txid], $abbr);

            $data = [];

            $data['confirmations'] = $result['confirmations'];

            if ((int)$data['confirmations'] >= 6) {

                $data['status'] = EnumGatewayStatus::PAID;

                GatewayStatus::create([
                    'status' => $data['status'],
                    'gateway_id' => $transaction->id
                ]);

                if ($data['status'] == EnumGatewayStatus::PAID AND $transaction->category == EnumGatewayCategory::POS) {
                    self::gatewayService()->{EnumGatewayPaymentCoin::TYPE[$transaction->user->gateway_key->payment_coin]}($transaction);
                }
            }
            $transaction->update($data);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex->getMessage();
        }
    }

    public function new(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'abbr' => [
                'required',
                Rule::in([Coin::getByAbbr("BRL")->abbr, Coin::getByAbbr("USD")->abbr])
            ]
        ]);

        try {
            DB::beginTransaction();

            $amount = floatval($request->amount);
            $gateway_coin = "BTC";
            $time = SysConfig::first()->time_gateway ?? 30;
            $tx = Uuid::uuid4()->toString();

            //criar address na moeda necessária
            $quote = $this->conversorService::FIAT2CRYPTO_MAX($amount, $gateway_coin, $request->abbr);

            $gateway = Gateway::create([
                'gateway_api_key_id' => 0,
                'coin_id' => Coin::getByAbbr($gateway_coin)->id,
                'address' => $this->newAddress($request->abbr),
                'amount' => $quote['amount'],
                'value' => $quote['quote'],

                'user_id' => '',
                'fiat_coin_id' => Coin::getByAbbr($request->abbr)->id,
                'fiat_amount' => $amount,
                'tx' => $tx,
                'status' => EnumGatewayStatus::NEWW,
                'type' => EnumGatewayType::PAYMENT,
                'tax' => 0,
                'category' => EnumGatewayCategory::CREDMINER,
                'time_limit' => Carbon::now()->addMinutes($time)
            ]);

            GatewayStatus::create([
                'status' => $gateway->status,
                'gateway_id' => $gateway->id
            ]);

            DB::commit();

            return response([
                'status' => $gateway->statusName,
                'payment' => $gateway->address,
                'coin' => $gateway->coin->abbr,
                'coin_name' => $gateway->coin->name,
                'amount' => $gateway->amount,
                'fiat' => $request->abbr,
                'fiat_amount' => number_format($gateway->fiat_amount, 2, ',', '.'),
                'qr_code' => strtolower(Coin::getByAbbr($gateway_coin)->name) . ':' . $gateway->address . '?amount=' . $gateway->amount
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function showGateway($tx)
    {
        return redirect(env('FRONT_URL') . '/gateway/tx/' . $tx);
    }

    public function list(Request $request)
    {
        try {
            $payments = Gateway::where('user_id', '=', $request->user->id)->orderBy('created_at', 'DESC')->paginate(10);
            return response($payments, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function showGatewayData($address_tx)
    {
        try {
            $payment = Gateway::where('address', $address_tx)->orWhere('tx', $address_tx)->first();
            return response([
                'message' => 'success',
                'payment' => $payment
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public static function gatewayService()
    {
        return new GatewayService(new BalanceService(), new ConversorService(), new TaxCoinService());
    }
}
