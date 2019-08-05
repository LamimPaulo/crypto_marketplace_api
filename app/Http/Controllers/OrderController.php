<?php

namespace App\Http\Controllers;

use App\Enum\EnumOperationType;
use App\Enum\EnumOrderStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Requests\ConvertRequest;
use App\Http\Requests\OrderRequest;
use App\Models\Coin;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserLevel;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
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

    public function show($order)
    {
        try {
            $orderStatus = Order::where('client_order_id', $order)->first();

            $transactions = Transaction::with('coin')
                ->where('tx', $order)
                ->where('user_id', auth()->user()->id)
                ->where('type', EnumTransactionType::SIDE[$orderStatus->side])
                ->orderBy('id');
            return response([
                'message' => trans('messages.general.success'),
                'status' => $orderStatus->status,
                'count' => 1,
                'order' => $transactions->first()
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            return response([
                'message' => $e->getMessage(),
                'order' => null,
                'count' => 0
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function orderBuy(OrderRequest $request)
    {
        try {
            $amount = $request->quantity;

            if ($amount <= 0) {
                throw new \Exception(trans('messages.amount_must_be_grater_than_zero'));
            }

            $pair = CoinPair::where('name', 'LIKE', $request->symbol)->first();

            $baseCoin = $pair->base_coin_id;
            $quoteCoin = $pair->quote_coin_id;

            $quotePrice = CoinCurrentPrice::where('coin_id', $baseCoin)->first()->price;
            $quoteQuantity = $amount * $quotePrice;

            if ($quoteQuantity <= $pair->min_trade_amount) {
                throw new \Exception(trans('messages.products.minimum_purchase_value_not_reached', ['amount' => $pair->min_trade_amount,'abbr' => 'BTC']));
            }

            $baseWallet = $this->checkWallets($baseCoin, EnumUserWalletType::PRODUCT);
            $quoteWallet = $this->checkWallets($quoteCoin, EnumUserWalletType::WALLET);

            $brokerageFee = UserLevel::where('id', auth()->user()->user_level_id)->first()->brokerage_fee;

            $from = UserWallet::where(['user_id' => auth()->user()->id, 'coin_id' => $quoteCoin])->first();

            $basePrice = CoinCurrentPrice::where('coin_id', $quoteCoin)->first()->price;
            $quotePrice = CoinCurrentPrice::where('coin_id', $baseCoin)->first()->price;
            $quoteQuantity = $amount * $quotePrice;

            $sumValueTransaction = floatval($quoteQuantity + ($quoteQuantity * ($brokerageFee / 100)));

            if (!(abs($sumValueTransaction) <= abs($from->balance))) {
                throw new \Exception(trans('messages.transaction.value_exceeds_balance'));
            }

            $api = new \GuzzleHttp\Client();

            $timestamp = Carbon::now()->timestamp * 1000;

            $queryString = "symbol=" . $request->symbol
                . "&side=BUY"
                . "&type=MARKET"
                . "&quantity=" . $request->quantity
                . "&recvWindow=5000"
                . "&timestamp=" . $timestamp;

            $signature = $this->signOperations($queryString);

            $response = $api->post("https://api.binance.com/api/v3/order?$queryString&signature=$signature", [
                'headers' => [
                    'X-MBX-APIKEY' => config('services.binance.key'),
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
//            $result = $this->getBuyOrder($request, $amount, $basePrice);
            return $this->storeBuyTransaction($result, $brokerageFee, $quoteQuantity, $quoteWallet, $quotePrice, $basePrice, $baseWallet, $amount);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $result = json_decode($response->getBody()->getContents());
            return response(['message' => $result->msg], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    private function storeBuyTransaction($order, $brokerageFee, $quoteQuantity, $quoteWallet, $quotePrice, $basePrice, $baseWallet, $amount)
    {
        try {
            DB::beginTransaction();
            $quoteTransaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $quoteWallet->coin_id,
                'wallet_id' => $quoteWallet->id,
                'amount' => $quoteQuantity,
                'status' => EnumOrderStatus::FILLED,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::ORDER,
                'confirmation' => 0,
                'tax' => $quoteQuantity * ($brokerageFee / 100),
                'tx' => $order['clientOrderId'],
                'info' => trans('info.coin_acquisition', ['abbr' => $baseWallet->coin->abbr]),
                'error' => '',
                'market' => $basePrice,
                'price' => $quotePrice
            ]);

            $quoteTransactionStatus = TransactionStatus::create([
                'status' => $quoteTransaction->status,
                'transaction_id' => $quoteTransaction->id,
            ]);

            $baseTransaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $baseWallet->coin_id,
                'wallet_id' => $baseWallet->id,
                'amount' => $amount,
                'status' => EnumOrderStatus::NEW,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::ORDER,
                'confirmation' => 0,
                'tax' => 0,
                'tx' => $order['clientOrderId'],
                'info' => trans('info.coin_acquisition', ['abbr' => $baseWallet->coin->abbr]),
                'error' => '',
                'market' => $basePrice,
                'price' => $quotePrice
            ]);

            TransactionStatus::create([
                'status' => $baseTransaction->status,
                'transaction_id' => $baseTransaction->id,
            ]);

            Order::create([
                'symbol' => $order['symbol'],
                'order_id' => $order['orderId'],
                'transaction_id' => $quoteTransaction->id,
                'client_order_id' => $order['clientOrderId'],
                'price' => $order['price'],
                'orig_qty' => $order['origQty'],
                'executed_qty' => $order['executedQty'],
                'cummulative_quote_qty' => $order['cummulativeQuoteQty'],
                'status' => $order['status'],
                'time_in_force' => $order['timeInForce'],
                'type' => $order['type'],
                'side' => $order['side'],
                'time' => $order['transactTime'],
            ]);

            $this->balanceService::decrements($quoteTransaction);

            DB::commit();
            return response([
                'message' => trans('messages.order_sent'),
                'transaction' => $quoteTransaction,
                'transactionStatus' => $quoteTransactionStatus
            ], Response::HTTP_CREATED);
        } catch (\Exception $ex) {
            DB::rollBack();

            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function orderSell(OrderRequest $request)
    {
        try {
            $amount = $request->quantity;

            if ($amount <= 0) {
                throw new \Exception(trans('messages.amount_must_be_grater_than_zero'));
            }

            $pair = CoinPair::where('name', 'LIKE', $request->symbol)->first();

            $baseCoin = $pair->base_coin_id;
            $quoteCoin = $pair->quote_coin_id;

            $quotePrice = CoinCurrentPrice::where('coin_id', $baseCoin)->first()->price;
            $quoteQuantity = $amount * $quotePrice;

            if ($quoteQuantity <= $pair->min_trade_amount) {
                throw new \Exception(trans('messages.minimum_sell_value_not_reached', ['amount' => $pair->min_trade_amount,'abbr' => 'BTC']));
            }

            $baseWallet = $this->checkWallets($baseCoin, EnumUserWalletType::PRODUCT);
            $quoteWallet = $this->checkWallets($quoteCoin, EnumUserWalletType::WALLET);

            $from = UserWallet::where(['user_id' => auth()->user()->id, 'coin_id' => $baseCoin])->first();

            $quotePrice = CoinCurrentPrice::where('coin_id', $quoteCoin)->first()->price;
            $basePrice = CoinCurrentPrice::where('coin_id', $baseCoin)->first()->price;

            if (!(abs($amount) <= abs($from->balance))) {
                throw new \Exception(trans('messages.transaction.value_exceeds_balance'));
            }

            $api = new \GuzzleHttp\Client();

            $timestamp = Carbon::now()->timestamp * 1000;

            $queryString = "symbol=" . $request->symbol
                . "&side=SELL"
                . "&type=MARKET"
                . "&quantity=" . $request->quantity
                . "&recvWindow=5000"
                . "&timestamp=" . $timestamp;

            $signature = $this->signOperations($queryString);

            $response = $api->post("https://api.binance.com/api/v3/order?$queryString&signature=$signature", [
                'headers' => [
                    'X-MBX-APIKEY' => config('services.binance.key'),
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $this->storeSellTransaction($result, $amount, $baseWallet, $basePrice, $quotePrice, $quoteWallet);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $result = json_decode($response->getBody()->getContents());
            return response(['message' => $result->msg], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    private function storeSellTransaction($order, $baseQuantity, $baseWallet, $basePrice, $quotePrice, $quoteWallet)
    {
        try {
            DB::beginTransaction();
            $baseTransaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $baseWallet->coin_id,
                'wallet_id' => $baseWallet->id,
                'amount' => $baseQuantity,
                'status' => EnumOrderStatus::FILLED,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::ORDER,
                'confirmation' => 0,
                'tx' => $order['clientOrderId'],
                'info' => 'Venda de ' . $baseWallet->coin->abbr,
                'error' => '',
                'market' => $quotePrice,
                'price' => $basePrice
            ]);

            $baseTransactionStatus = TransactionStatus::create([
                'status' => $baseTransaction->status,
                'transaction_id' => $baseTransaction->id,
            ]);

            $brokerageFee = UserLevel::where('id', auth()->user()->user_level_id)->first()->brokerage_fee;
            $quotePrice_ = CoinCurrentPrice::where('coin_id', $baseWallet->coin_id)->first()->price;
            $quoteQuantity = $baseQuantity * $quotePrice_;

            $quoteTransaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $quoteWallet->coin_id,
                'wallet_id' => $quoteWallet->id,
                'amount' => $quoteQuantity - ($quoteQuantity * ($brokerageFee / 100)),
                'status' => EnumOrderStatus::NEW,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::ORDER,
                'confirmation' => 0,
                'tax' => $quoteQuantity * ($brokerageFee / 100),
                'tx' => $order['clientOrderId'],
                'info' => 'Venda de ' . $baseWallet->coin->abbr,
                'error' => '',
                'market' => $quotePrice,
                'price' => $basePrice
            ]);

            TransactionStatus::create([
                'status' => $quoteTransaction->status,
                'transaction_id' => $quoteTransaction->id,
            ]);

            Order::create([
                'symbol' => $order['symbol'],
                'order_id' => $order['orderId'],
                'transaction_id' => $baseTransaction->id,
                'client_order_id' => $order['clientOrderId'],
                'price' => $order['price'],
                'orig_qty' => $order['origQty'],
                'executed_qty' => $order['executedQty'],
                'cummulative_quote_qty' => $order['cummulativeQuoteQty'],
                'status' => $order['status'],
                'time_in_force' => $order['timeInForce'],
                'type' => $order['type'],
                'side' => $order['side'],
                'time' => $order['transactTime'],
            ]);

            $this->balanceService::decrements($baseTransaction);

            DB::commit();
            return response([
                'message' => trans('messages.order_sent'),
                'transaction' => $baseTransaction,
                'transactionStatus' => $baseTransactionStatus
            ], Response::HTTP_CREATED);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function signOperations($string)
    {
        return hash_hmac('sha256', $string, config('services.binance.secret'));
    }

    private function checkWallets($coin_id, $wallet_type)
    {
        $wallet = UserWallet::with('coin')
            ->whereHas('coin', function ($coin) use ($coin_id) {
                return $coin->where('id', $coin_id);
            })
            ->where('type', $wallet_type)
            ->where(['user_id' => auth()->user()->id, 'is_active' => 1])->first();

        if (!$wallet) {
            $address = Uuid::uuid4()->toString();
            $wallet = UserWallet::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $coin_id,
                'type' => $wallet_type,
                'address' => $coin_id == 1 ? OffScreenController::post(EnumOperationType::CREATE_ADDRESS, null, 'BTC') : $address,
                'balance' => 0
            ]);
        }

        return $wallet;
    }

    public function convertBuy(Request $request)
    {
        if ($request->quote === 'LQXD') {
            return response(['message' => 'Moeda não disponível para compra'], Response::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'amount' => 'required|numeric',
            'base' => 'required',
        ],[
            'amount.required' => 'A quantidade é obrigatória',
            'amount.numeric' => 'A quantidade deve ser um número válido (utilize apenas números e ponto)',
            'base.required' => 'A moeda base é obrigatória',
        ]);

        $amount = (float)$request->amount;
        $user_fiat_abbr = auth()->user()->country_id === 31 ? 'BRL' : 'USD';
        $fiat_coin = Coin::with('quote')->where('abbr', $user_fiat_abbr)->first();

        if ($request->base === $user_fiat_abbr) {
            return response([
                'message' => trans('messages.coin.must_be_distinct')
            ], Response::HTTP_BAD_REQUEST);
        }

        $base_coin = Coin::with([
            'quote' => function ($coin) use ($fiat_coin) {
                return $coin->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
            }
        ])->where('abbr', $request->base)->first();

        $result_buy = $base_coin->quote[0]->buy_quote * $amount;
        $dec_point = $fiat_coin->abbr === 'BRL' ? ',' : '.';
        $thousando_sep = $fiat_coin->abbr === 'BRL' ? '.' : ',';
        return [
            'amount' => number_format($result_buy, 2, $dec_point, $thousando_sep),
            'message' => $this->balanceService->verifyBalance($result_buy, $user_fiat_abbr)
        ];

    }

    public function convertBuyAmount(ConvertRequest $request)
    {
        if ($request->quote === 'LQXD') {
            return response(['message' => 'Moeda não disponível para compra'], Response::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'amount' => 'required|numeric',
            'base' => 'required',
        ],[
            'amount.required' => 'A quantidade é obrigatória',
            'amount.numeric' => 'A quantidade deve ser um número válido (utilize apenas números e ponto)',
            'base.required' => 'A moeda base é obrigatória',
        ]);

        $user_fiat_abbr = auth()->user()->country_id === 31 ? 'BRL' : 'USD';

        if ($request->base === $user_fiat_abbr) {
            return response(['message' => trans('messages.coin.must_be_distinct')], Response::HTTP_BAD_REQUEST);
        }

        $amount = (float)$request->amount;

        $fiat_coin = Coin::with('quote')->where('abbr', $user_fiat_abbr)->first();

        $base_coin = Coin::with([
            'quote' => function ($coin) use ($fiat_coin) {
                return $coin->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
            }
        ])->where('abbr', $request->base)->first();

        $result_buy = $base_coin->quote[0]->buy_quote * $amount;

        if (!$this->balanceService->verifyBalance($result_buy, $user_fiat_abbr)) {
            return response(['message' => trans('messages.wallet.insuficient_balance')], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();
            $uuid = Uuid::uuid4();

            $wallet_out = UserWallet::where(['coin_id' => $fiat_coin->id, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first()->id;
            $wallet_in = UserWallet::where(['coin_id' => $base_coin->id, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first()->id;

            $transaction_out = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $fiat_coin->id,
                'wallet_id' => $wallet_out,
                'amount' => $result_buy,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => '',
                'error' => '',
                'price' => 1,
                'market' => 1
            ]);

            TransactionStatus::create([
                'status' => $transaction_out->status,
                'transaction_id' => $transaction_out->id,
            ]);

            $transaction_in = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $base_coin->id,
                'wallet_id' => $wallet_in,
                'amount' => $amount,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => '',
                'error' => '',
                'price' => $base_coin->quote[0]->average_quote,
                'market' => $base_coin->quote[0]->buy_quote
            ]);

            TransactionStatus::create([
                'status' => $transaction_in->status,
                'transaction_id' => $transaction_in->id,
            ]);

            $this->balanceService::increments($transaction_in);
            $this->balanceService::decrements($transaction_out);

            DB::commit();
            return response([
                'message' => trans('messages.transaction.conversion_success')
            ], Response::HTTP_CREATED);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public
    function convert(ConvertRequest $request)
    {
        if ($request->base === $request->quote) {
            return response(['message' => trans('messages.coin.must_be_distinct')], Response::HTTP_BAD_REQUEST);
        }

        if ($request->base === 'LQXDD') {
            return response(['message' => 'Moeda não disponível para venda'], Response::HTTP_BAD_REQUEST);
        }

        $amount = (float)$request->amount;
        $user_fiat_abbr = auth()->user()->country_id === 31 ? 'BRL' : 'USD';
        $fiat_coin = Coin::with('quote')->where('abbr', $user_fiat_abbr)->first();

        $base_coin = Coin::with([
            'quote' => function ($coin) use ($fiat_coin) {
                return $coin->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
            }
        ])->where('abbr', $request->base)->first();

        $quote_coin = Coin::with([
            'quote' => function ($coin) use ($fiat_coin) {
                return $coin->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
            }
        ])->where('abbr', $request->quote)->first();

        if ($base_coin->is_crypto AND $quote_coin->is_crypto) {
            $result_sell = $base_coin->quote[0]->sell_quote * $amount;
            $result_amount = $result_sell / $quote_coin->quote[0]->buy_quote;
            return [
                'amount' => number_format($result_amount, $quote_coin->decimal, '.', ''),
                'message' => $this->balanceService->verifyBalance($amount, $base_coin->abbr)
            ];
        }

        if ($base_coin->is_crypto AND !$quote_coin->is_crypto) {
            $result_sell = $base_coin->quote[0]->sell_quote * $amount;
            $dec_point = $fiat_coin->abbr === 'BRL' ? ',' : '.';
            $thousando_sep = $fiat_coin->abbr === 'BRL' ? '.' : ',';
            return [
                'amount' => number_format($result_sell, $quote_coin->decimal, $dec_point, $thousando_sep),
                'message' => $this->balanceService->verifyBalance($amount, $base_coin->abbr)
            ];
        }

        if (!$base_coin->is_crypto AND $quote_coin->is_crypto) {
            $result_buy = $amount / $quote_coin->quote[0]->buy_quote;

            return [
                'amount' => number_format($result_buy, $quote_coin->decimal, '.', ''),
                'message' => $this->balanceService->verifyBalance($amount, $base_coin->abbr)
            ];
        }
    }

    public function convertAmount(ConvertRequest $request)
    {

        if ($request->base === 'LQXD') {
            return response(['message' => 'Moeda não disponível para venda'], Response::HTTP_BAD_REQUEST);
        }

        if ($request->base === $request->quote) {
            return response(['message' => trans('messages.coin.must_be_distinct')], Response::HTTP_BAD_REQUEST);
        }

        $amount = (float)$request->amount;
        $user_fiat_abbr = auth()->user()->country_id === 31 ? 'BRL' : 'USD';
        $fiat_coin = Coin::with('quote')->where('abbr', $user_fiat_abbr)->first();

        $base_coin = Coin::with([
            'quote' => function ($coin) use ($fiat_coin) {
                return $coin->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
            }
        ])->where('abbr', $request->base)->first();

        $quote_coin = Coin::with([
            'quote' => function ($coin) use ($fiat_coin) {
                return $coin->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
            }
        ])->where('abbr', $request->quote)->first();


        if (!$base_coin->is_crypto AND !$quote_coin->is_crypto) {
            return response([
                'message' => trans('messages.coin.can_not_be_converted'),
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($base_coin->is_crypto AND $quote_coin->is_crypto) {
            $result_sell = $base_coin->quote[0]->sell_quote * $amount;
            $result_buy = $result_sell / $quote_coin->quote[0]->buy_quote;

            $result = [
                'amount_buy' => $result_buy,
                'sell_current' => $base_coin->quote[0]->average_quote,
                'sell_quote' => $base_coin->quote[0]->sell_quote,
                'buy_current' => $quote_coin->quote[0]->average_quote,
                'buy_quote' => $quote_coin->quote[0]->sell_quote,
            ];
        }

        if ($base_coin->is_crypto AND !$quote_coin->is_crypto) {
            $result_buy = $base_coin->quote[0]->sell_quote * $amount;

            $result = [
                'amount_buy' => $result_buy,
                'sell_current' => $base_coin->quote[0]->average_quote,
                'sell_quote' => $base_coin->quote[0]->sell_quote,
                'buy_current' => 1,
                'buy_quote' => 1,
            ];
        }

        if (!$base_coin->is_crypto AND $quote_coin->is_crypto) {
            $result_buy = $amount / $quote_coin->quote[0]->buy_quote;
            $result = [
                'amount_buy' => $result_buy,
                'buy_current' => $quote_coin->quote[0]->average_quote,
                'buy_quote' => $quote_coin->quote[0]->sell_quote,
                'sell_current' => 1,
                'sell_quote' => 1,
            ];
        }


        if (!$this->balanceService->verifyBalance($amount, $base_coin->abbr)) {
            return response(['message' => trans('messages.wallet.insuficient_balance')], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();
            $uuid = Uuid::uuid4();

            $wallet_out = UserWallet::where(['coin_id' => $base_coin->id, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first()->id;
            $wallet_in = UserWallet::where(['coin_id' => $quote_coin->id, 'user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->first()->id;
            $transaction_out = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $base_coin->id,
                'wallet_id' => $wallet_out,
                'amount' => $amount,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => '',
                'error' => '',
                'price' => $result['sell_current'],
                'market' => $result['sell_quote']
            ]);

            TransactionStatus::create([
                'status' => $transaction_out->status,
                'transaction_id' => $transaction_out->id,
            ]);

            $transaction_in = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $quote_coin->id,
                'wallet_id' => $wallet_in,
                'amount' => $result['amount_buy'],
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => $uuid->toString(),
                'info' => '',
                'error' => '',
                'price' => $result['buy_current'],
                'market' => $result['buy_quote']
            ]);

            TransactionStatus::create([
                'status' => $transaction_in->status,
                'transaction_id' => $transaction_in->id,
            ]);

            $this->balanceService::decrements($transaction_out);
            $this->balanceService::increments($transaction_in);

            DB::commit();
            return response([
                'message' => trans('messages.transaction.conversion_success')
            ], Response::HTTP_CREATED);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function conversionList()
    {
        try {
            $transactions = Transaction::with('coin')
                ->where('user_id', auth()->user()->id)
                ->where('category', EnumTransactionCategory::CONVERSION)
                ->orderBy('created_at', 'DESC');

            return response([
                'message' => trans('messages.general.success'),
                'count' => $transactions->count(),
                'transactions' => $transactions->take(10)->get()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function conversion($tx)
    {
        try {
            $transactions = Transaction::with('coin')
                ->where('tx', $tx)
                ->where('user_id', auth()->user()->id)
                ->orderBy('id');
            return response([
                'message' => trans('messages.general.success'),
                'count' => $transactions->count(),
                'order' => $transactions->get()
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            return response([
                'message' => $e->getMessage(),
                'order' => null,
                'count' => 0
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function myCoinsList()
    {
        $coins = Coin::whereHas('wallets', function ($wallets) {
            return $wallets->where('user_id', auth()->user()->id)->where('type', EnumUserWalletType::WALLET);
        })->get();
        return $coins;
    }


    public function testOrder($quantity)
    {
        try {
            $api = new \GuzzleHttp\Client();

            $timestamp = Carbon::now()->timestamp * 1000;

            $queryString = "symbol=DASHBTC"
                . "&side=BUY"
                . "&type=MARKET"
                . "&quantity=" . $quantity
                . "&recvWindow=5000"
                . "&timestamp=" . $timestamp;

            $signature = $this->signOperations($queryString);

            $response = $api->post("https://api.binance.com/api/v3/order/test?$queryString&signature=$signature", [
                'headers' => [
                    'X-MBX-APIKEY' => config('services.binance.key'),
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result;
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBody = json_decode($response->getBody()->getContents(), true);
            return $responseBody;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function allOrders()
    {
        try {
            $api = new \GuzzleHttp\Client();

            $timestamp = Carbon::now()->timestamp * 1000;

            $queryString = "symbol=DASHBTC"
                . "&limit=10"
                . "&timestamp=" . $timestamp;

            $signature = $this->signOperations($queryString);

            $response = $api->post("https://api.binance.com/api/v3/api/v3/myTrades?$queryString&signature=$signature", [
                'headers' => [
                    'X-MBX-APIKEY' => config('services.binance.key'),
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBody = json_decode($response->getBody()->getContents(), true);
            return $responseBody;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function profile()
    {
        try {
            $api = new \GuzzleHttp\Client();

            $timestamp = Carbon::now()->timestamp * 1000;

            $queryString = "timestamp=" . $timestamp;

            $signature = $this->signOperations($queryString);

            $response = $api->get("https://api.binance.com/api/v3/account?$queryString&signature=$signature", [
                'headers' => [
                    'X-MBX-APIKEY' => config('services.binance.key'),
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result;

        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex->getMessage();
        }
    }

    public function getOrder($clientId)
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

    public function command()
    {
        try {
            $transactions = Transaction::where('status', EnumTransactionsStatus::PENDING)
                ->where('type', EnumTransactionType::IN)
                ->where('category', EnumTransactionCategory::ORDER)
                ->orderBy('created_at')->get();

            foreach ($transactions as $transaction) {
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

                        $this->balanceService::increments($transaction);
                        DB::commit();
                    }
                }
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex->getMessage();
        }
    }

}
