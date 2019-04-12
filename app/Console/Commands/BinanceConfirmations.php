<?php

namespace App\Console\Commands;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BinanceConfirmations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:binanceconfirmations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Binance Orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->updateOrders();
    }

    public function updateOrders()
    {
        try {
            $balanceService = new BalanceService();

            $transactions = Transaction::where('status', EnumTransactionsStatus::PENDING)
                ->where('type', EnumTransactionType::IN)
                ->where('category', EnumTransactionCategory::ORDER)
                ->orderBy('created_at')->get();

            foreach ($transactions as $transaction) {
                DB::beginTransaction();
                $order = Order::where('client_order_id', $transaction->tx)->first();
                if ($order) {
                    $remoteOrder = $this->getOrder($order->client_order_id);
                    if ($remoteOrder['status'] == "FILLED") {
                        $updatedOrder = [
                            'symbol' => $remoteOrder['symbol'],
                            'order_id' => $remoteOrder['orderId'],
                            'price' => $remoteOrder['price'],
                            'orig_qty' => $remoteOrder['origQty'],
                            'executed_qty' => $remoteOrder['executedQty'],
                            'cummulative_quote_qty' => $remoteOrder['cummulativeQuoteQty'],
                            'status' => $remoteOrder['status'],
                            'time_in_force' => $remoteOrder['timeInForce'],
                            'type' => $remoteOrder['type'],
                            'side' => $remoteOrder['side'],
                            'time' => Carbon::createFromTimestamp($remoteOrder['time'] / 1000)->toDateTimeString(),
                            'update_time' => Carbon::createFromTimestamp($remoteOrder['updateTime'] / 1000)->toDateTimeString(),
                            'is_working' => $remoteOrder['isWorking']
                        ];
                        $order->fill($updatedOrder);
                        $order->save();

                        $transaction->status = EnumTransactionsStatus::SUCCESS;
                        $transaction->save();

                        TransactionStatus::create([
                            'status' => $transaction->status,
                            'transaction_id' => $transaction->id,
                        ]);
                        $balanceService::increments($transaction);
                        DB::commit();
                    }
                }
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex->getMessage();
        }
    }

    private function getOrder($clientId)
    {
        try {
            $order = Order::where('client_order_id', $clientId)->firstOrFail();
            $api = new \GuzzleHttp\Client();

            $timestamp = Carbon::now()->timestamp * 1000;

            $queryString = "symbol=" . $order->symbol
                . "&origClientOrderId=" . $clientId
                . "&timestamp=" . $timestamp;

            $signature = $this->signOperations($queryString);

            $response = $api->get("https://api.binance.com/api/v3/order?$queryString&signature=$signature", [
                'headers' => [
                    'X-MBX-APIKEY' => config('services.binance.key'),
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result;

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    private function signOperations($string)
    {
        return hash_hmac('sha256', $string, config('services.binance.secret'));
    }
}
