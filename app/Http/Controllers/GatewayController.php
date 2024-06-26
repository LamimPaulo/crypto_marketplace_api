<?php

namespace App\Http\Controllers;

use App\Enum\EnumGatewayCategory;
use App\Enum\EnumGatewayPaymentCoin;
use App\Enum\EnumGatewayStatus;
use App\Enum\EnumGatewayType;
use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Requests\CredminerGatewayRequest;
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
                'category' => EnumGatewayCategory::POS,
                'time_limit' => Carbon::now()->addMinutes($time)
            ]);

            GatewayStatus::create([
                'status' => $gateway->status,
                'gateway_id' => $gateway->id
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'address' => $gateway->address,
                'amount' => $gateway->amount,
                'coin' => $gateway->coin->abbr,
                'coin_name' => $gateway->coin->name,
                'fiat_amount' => $gateway->fiat_amount,
                'fiat' => number_format($gateway->fiat_amount, 2, ',', '.'),
                'qr_code' => $request->abbr == 'BCH' ? $gateway->address . '?amount=' . $gateway->amount : strtolower(Coin::getByAbbr($request->abbr)->name) . ':' . $gateway->address . '?amount=' . $gateway->amount
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
            $api = new \GuzzleHttp\Client();
            $url = config("services.gateway.{$abbr}");
            $response = $api->post($url);
            return $response->getBody()->getContents();
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
                "address" => $gateway->address,
                "tx" => $gateway->tx,
                "created" => $gateway->created_at,
                "confirmations" => $gateway->confirmations,
                "amount" => sprintf('%.8f', floatval($gateway->amount)),
                "coin" => $gateway->coin->abbr,
                "coin_name" => $gateway->coin->name,
                "fiat_amount" => $gateway->fiat_amount,
                "fiat" => number_format($gateway->fiat_amount, 2, ',', '.'),
                "fiat_abbr" => $gateway->fiat_coin->abbr,
                'qr_code' => strtoupper($gateway->coin->abbr) == 'BCH' ? $gateway->address . '?amount=' . sprintf("%.5f", $gateway->amount) : strtolower(Coin::getByAbbr($gateway->coin->abbr)->name) . ':' . $gateway->address . '?amount=' . sprintf("%.5f", $gateway->amount),
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

            if (!$gateway) {
                return false;
            }

            if (in_array($gateway->status, EnumGatewayStatus::NOTIFY)) {

                if ($gateway->status == EnumGatewayStatus::EXPIRED) {
                    $amount = abs($transaction['amount']);
                    $status = self::setExpiredStatus($gateway, $amount);
                }

                if ($gateway->status == EnumGatewayStatus::NEWW) {
                    $amount = abs($transaction['amount']);
                    $status = self::setStatus($gateway, $amount);
                }

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
     * @param mixed $expected
     * @param mixed $received
     * @return int
     */
    private static function setStatus($expected, $received)
    {
        $expected->amount = sprintf("%.8f", $expected->amount);
        $received = sprintf("%.8f", $received);

        if ($received == $expected->amount) {
            return EnumGatewayStatus::DONE;
        } else if ($received < $expected->amount) {
            return EnumGatewayStatus::UNDERPAID;
        } else if ($received > $expected->amount) {
            return EnumGatewayStatus::OVERPAID;
        }
        return $expected->status;
    }

    /**
     *
     * @param mixed $expected
     * @param mixed $received
     * @return int
     */
    private static function setExpiredStatus($expected, $received)
    {
        $expected->amount = sprintf("%.8f", $expected->amount);
        $received = sprintf("%.8f", $received);

        if ($received == $expected->amount) {
            return EnumGatewayStatus::DONEEXPIRED;
        } else if ($received < $expected->amount) {
            return EnumGatewayStatus::UNDERPAIDEXPIRED;
        } else if ($received > $expected->amount) {
            return EnumGatewayStatus::OVERPAIDEXPIRED;
        }
        return $expected->status;
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
            ->where('confirmations', '<', 6)
            ->whereRaw('LENGTH(txid) > 36')
            ->whereIn('status', EnumGatewayStatus::CONFIRMATION)
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

                $data['status'] =
                    EnumGatewayStatus::DONE == $data['status'] ? EnumGatewayStatus::PAID :
                        (EnumGatewayStatus::DONEEXPIRED == $data['status'] ? EnumGatewayStatus::PAIDEXPIRED :
                            $data['status']);

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

    public function new(CredminerGatewayRequest $request)
    {
        try {

            DB::beginTransaction();

            $amount = floatval($request->fiat_amount);
            $time = SysConfig::first()->time_gateway ?? 30;
            $tx = Uuid::uuid4()->toString();

            $quote = $this->conversorService::FIAT2CRYPTO_MIN($amount, $request->crypto_abbr, $request->fiat_abbr);

            $gateway = Gateway::create([
                'gateway_api_key_id' => 0,
                'coin_id' => Coin::getByAbbr($request->crypto_abbr)->id,
                'address' => $this->newAddress($request->crypto_abbr),
                'amount' => sprintf("%.5f", $quote['amount']),
                'value' => $quote['quote'],

                'user_id' => '',
                'fiat_coin_id' => Coin::getByAbbr($request->fiat_abbr)->id,
                'fiat_amount' => $amount,
                'tx' => $tx,
                'status' => EnumGatewayStatus::NEWW,
                'type' => EnumGatewayType::PAYMENT,
                'tax' => 0,
                'category' => $request->type ?? EnumGatewayCategory::CREDMINER,
                'time_limit' => Carbon::now()->addMinutes($time)
            ]);

            GatewayStatus::create([
                'status' => $gateway->status,
                'gateway_id' => $gateway->id
            ]);

            DB::commit();

            return response([
                'status' => $gateway->statusName,
                'status_id' => $gateway->status,
                'address' => $gateway->address,
                'coin' => $gateway->coin->abbr,
                'coin_name' => $gateway->coin->name,
                'amount' => sprintf("%.5f", $gateway->amount),
                'fiat' => $request->fiat_abbr,
                'fiat_amount' => number_format($gateway->fiat_amount, 2, ',', '.'),
                'qr_code' => $request->crypto_abbr == 'BCH' ? $gateway->address . '?amount=' . sprintf("%.5f", $gateway->amount) : strtolower(Coin::getByAbbr($request->crypto_abbr)->name) . ':' . $gateway->address . '?amount=' . sprintf("%.5f", $gateway->amount),
                'time_limit' => $gateway->time_limit
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

            if (!$payment) {
                throw new \Exception("Pagamento não encontrado!");
            }

            $payment->qr_code = strtoupper($payment->coin->abbr) == 'BCH' ? $payment->address . '?amount=' . sprintf("%.5f", $payment->amount) : strtolower(Coin::getByAbbr($payment->coin->abbr)->name) . ':' . $payment->address . '?amount=' . sprintf("%.5f", $payment->amount);

            unset($payment->coin);

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

    public function gatewayDataList(Request $request)
    {
        try {
            $result = [];

            foreach (array_unique($request->addresses) as $address) {
                $payment = Gateway::where('address', $address)->first();
                if ($payment) {
                    $result[] = [
                        'address' => $address,
                        'received' => $payment->received,
                        'status' => $payment->status,
                        'statusName' => $payment->statusName,
                    ];
                } else {
                    $result[] = [
                        'address' => $address,
                        'received' => 0,
                        'status' => 0,
                        'statusName' => EnumGatewayStatus::SITUATION[EnumGatewayStatus::NOTFOUND],
                    ];
                }
            }

            return response([
                'message' => 'success',
                'payments' => $result
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
