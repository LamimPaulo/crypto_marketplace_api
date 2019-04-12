<?php

namespace App\Http\Controllers;

use App\Enum\EnumGatewayCategory;
use App\Enum\EnumGatewayPaymentCoin;
use App\Enum\EnumGatewayStatus;
use App\Enum\EnumGatewayType;
use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionType;
use App\Models\Coin;
use App\Models\Gateway;
use App\Models\GatewayApiKey;
use App\Models\GatewayStatus;
use App\Models\SysConfig;
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
            $key = GatewayApiKey::where('api_key', '=', $request->get('api_key'))->first();

            if (is_null($key)) {
                throw new \Exception(trans('messages.auth.invalid_key'));
            }

            return response([
                'message' => trans('messages.auth.invalid_key')
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_OK);
        }
    }

    public function create(Request $request)
    {
        $tx = Uuid::uuid4()->toString();

        $gateway = Gateway::create([
            'address' => $this->address,
            'user_id' => $request->get('user_id'),
            'fiat_coin_id' => $request->get('fiat_coin_id'),
            'coin_id' => $request->get('coin_id'),
            'amount' => $request->get('amount'),
            'fiat_amount' => $request->get('fiat_amount'),
            'value' => $request->get('value'),
            'tx' => $tx,
            'status' => EnumGatewayStatus::NEWW,
            'type' => $request->get('type'),
            'tax' => $request->get('tax'),
            'category' => EnumGatewayCategory::PAYMENT
        ]);

        GatewayStatus::create([
            'status' => $gateway->status,
            'gateway_id' => $gateway->id
        ]);

        return $gateway;
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required'
            ]);

            if ($request->user->country_id == 31) {
                $quote = $this->conversorService::BRLTAX2BTCMIN($request->get('amount'));
                $fiatCoin = Coin::getByAbbr('BRL')->id;
            }

            if ($request->user->country_id != 31) {
                $quote = $this->conversorService::USDTAX2BTCMIN($request->get('amount'));
                $fiatCoin = Coin::getByAbbr('USD')->id;
            }

            $request->request->add([
                'amount' => $quote['amount'],
                'fiat_amount' => $request->get('amount'),
                'fiat_coin_id' => $fiatCoin,
                'value' => $quote['current'],
                'tax' => 0
            ]);

            $request->request->add(['user_id' => $request->user->id]);
            $request->request->add(['coin_id' => 1]);
            $request->request->add(['type' => EnumGatewayType::PAYMENT]);

            $gateway = $this->BTC($request);
            return response([
                'payment' => $gateway->tx
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    protected function BTC(Request $request)
    {
        $this->address = env('APP_ENV') == 'local' ? Uuid::uuid4()->toString() : OffScreenController::post(EnumOperationType::CREATE_ADDRESS, null, 'BTC');
        return $this->create($request);
    }

    public static function show($address)
    {
        return Gateway::where('address', $address)->first();
    }

    public function status($id)
    {
        try {
            $gateway = Gateway::where('tx', '=', $id)->first();

            if (!isset($gateway->status)) {
                return response(['message' => trans('messages.gateway.payment_not_found')], Response::HTTP_NOT_FOUND);
            }

            return response([
                'payment' => [
                    "status" => EnumGatewayStatus::SITUATION[$gateway->status],
                    "amount" => sprintf('%.8f', floatval($gateway->amount)),
                    "address" => $gateway->address,
                    "tx" => $gateway->tx,
                    "created" => $gateway->created_at,
                    "confirmations" => $gateway->confirmations
                ]
            ]);
        } catch (\Exception $ex) {
            return response([
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

        if ($transaction->amount === $expected) {
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
            $gateway = Gateway::where('tx', '=', $payment)
                ->where('type', '=', EnumGatewayType::PAYMENT)
                ->first();

            if (!is_null($gateway)) {
                $total = ($gateway->amount + $gateway->taxas);
                $histStatus = $gateway->histStatus;
                $coin = $gateway->coin;
                $total = sprintf('%.' . $coin->decimal . 'f', floatval($total));

                foreach ($histStatus as $key => $value) {
                    $histStatus[$key]->name = EnumGatewayStatus::SITUATION[$histStatus[$key]->status];
                }

                $sysConfig = SysConfig::first();

                $created = Carbon::parse($gateway->created_at)->addMinutes($sysConfig->time_gateway);
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
                return $user->with(['api_key', 'level']);
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

                $data['status'] = EnumGatewayStatus::DONE == $transaction->status ? EnumGatewayStatus::PAID : $transaction->status;

                GatewayStatus::create([
                    'status' => $data['status'],
                    'gateway_id' => $transaction->id
                ]);

                if ($data['status'] == EnumGatewayStatus::PAID) {
                    self::gatewayService()->{EnumGatewayPaymentCoin::TYPE[$transaction->user->api_key->payment_coin]}($transaction);
                }
            }

            $transaction->update($data);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex->getMessage();
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

    public static function gatewayService()
    {
        return new GatewayService(new BalanceService(), new ConversorService(), new TaxCoinService());
    }
}
