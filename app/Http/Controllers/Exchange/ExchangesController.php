<?php

namespace App\Http\Controllers\Exchange;

use App\Http\Controllers\Controller;
use App\Models\Exchange\Exchanges;
use App\Models\Exchange\ExchangeTrade;

//use ccxt\Exchange;

class ExchangesController extends Controller
{
    public function index()
    {
        return $this->exchanges();
    }

    public function execute()
    {
        $symbol = 'BTC/BRL';
        $trades = $this->comparison($symbol);
        $min_profit = 0.5;

        foreach ($trades as $t) {
            if ($t['profit_percentage'] >= $min_profit) {
                $amount = $t['base_quantity'] >= $t['quote_quantity'] ? $t['quote_quantity'] : $t['quote_quantity'];

                $tradeOut = ExchangeTrade::firstOrNew([
                    'symbol' => $symbol,
                    'type' => $t['base_type'],
                    'side' => $t['base_side'],
                    'amount' => $amount,
                    'total' => $amount * $t['base_price'],
                    'price' => $t['base_price'],
                    'base_exchange' => $t['base_exchange'],
                    'quote_exchange' => $t['quote_exchange'],
                    ]);

                $tradeOut->fill([
                    'status' => 'FILLED',
                    'fee' => $amount * $t['base_comission'],
                    'profit' => $amount * ($t['profit_percentage'] / 100),
                    'profit_percent' => $t['profit_percentage'],
                    'base_price' => $t['base_price'],
                    'quote_price' => $t['quote_price']
                ]);

                $tradeOut->save();

//                $tradeIn = ExchangeTrade::create([
//                    'symbol' => $symbol,
//                    'type' => $t['quote_type'],
//                    'side' => $t['quote_side'],
//                    'amount' => $amount,
//                    'total' => $amount * $t['quote_price'],
//                    'price' => $t['quote_price'],
//                    'base_exchange' => $t['quote_exchange'],
//                    'quote_exchange' => $t['base_exchange'],
//                    'status' => 'FILLED',
//                    'fee' => $amount * $t['quote_comission'],
//                    'profit' => $amount * ($t['profit_percentage'] / 100),
//                    'profit_percent' => $t['profit_percentage'],
//                    'base_price' => $t['quote_price'],
//                    'quote_price' => $t['base_price']
//                ]);
//
//                $tradeIn->save();
            }
        }
    }

    public function comparison($symbol = 'BTC/BRL')
    {
        $result = [];
        $exchanges = collect($this->exchanges($symbol));
        //sell - bid
        foreach ($exchanges as $i => $exchange) {
            $base_comission = Exchanges::where('name', $exchange['exchange'])->first()->comission->value;
            foreach ($exchanges->where('exchange', '<>', $exchange['exchange']) as $j => $exchange_child) {
                if (($exchange_child['sell'] - $exchange['buy']) > 0) {
                    $quote_comission = Exchanges::where('name', $exchange_child['exchange'])->first()->comission->value;
                    $result[] = [
                        'base_side' => 'sell',
                        'base_exchange' => $exchange_child['exchange'],
                        'base_price' => $exchange_child['sell'],
                        'base_quantity' => $exchange_child['sell_quantity'],
                        'base_type' => $exchange['orderType'],
                        'base_comission' => $base_comission / 100,
                        'quote_side' => 'buy',
                        'quote_exchange' => $exchange['exchange'],
                        'quote_price' => $exchange['buy'],
                        'quote_quantity' => $exchange['buy_quantity'],
                        'quote_type' => $exchange_child['orderType'],
                        'quote_comission' => $quote_comission / 100,
                        'profit_percentage' => sprintf('%.2f', ($exchange_child['sell'] - $exchange['buy']) * 100 / $exchange['buy']),

                    ];
                }
            }
        }
        //buy - ask
//        foreach ($exchanges as $i => $exchange) {
//            $comission = Exchanges::where('name', $exchange['exchange'])->first()->comission->value;
//            foreach ($exchanges->where('exchange', '<>', $exchange['exchange']) as $j => $exchange_child) {
//                if (($exchange['sell'] - $exchange_child['buy']) > 0) {
//                    $result[] = [
//                        'side' => 'buy',
//                        'base_exchange' => $exchange_child['exchange'],
//                        'base_price' => $exchange_child['buy'],
//                        'quote_exchange' => $exchange['exchange'],
//                        'quote_price' => $exchange['sell'],
//                        'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
//                        'percentage' => sprintf('%.2f', ($exchange['sell'] - $exchange_child['buy']) * 100 / $exchange['sell']),
//                        'comission' => $comission / 100
//                    ];
//                }
//            }
//        }
        return collect($result);
    }

    private function best_market_prices($exchange, $symbol)
    {
        $orderbook = $exchange->fetch_order_book($symbol);

        $bid = count($orderbook['bids']) ? $orderbook['bids'][0][0] : null;
        $bid_quantity = count($orderbook['bids']) ? $orderbook['bids'][0][1] : null;
        $ask = count($orderbook['asks']) ? $orderbook['asks'][0][0] : null;
        $ask_quantity = count($orderbook['asks']) ? $orderbook['asks'][0][1] : null;

        $spread = ($bid && $ask) ? $ask - $bid : null;
        $result = [
            'sell' => $bid,
            'sell_quantity' => $ask_quantity,
            'buy' => $ask,
            'buy_quantity' => $bid_quantity,
            'spread' => $spread,
        ];
        return $result;
    }

    public function exchanges($symbol = 'BTC/BRL')
    {
        $dbExchanges = Exchanges::where('is_active', true)->get();
        $exchanges = [];

        foreach ($dbExchanges as $db) {
            $exchange_class = "\\ccxt\\$db->name";
            $exchange = new $exchange_class([
//                'apiKey' => env("_KEY"),
//                'secret' => env("_SECRET"),
                'timeout' => 30000,
                'enableRateLimit' => true,
            ]);

            $orderType = 'market';

            if (!$exchange->has['createMarketOrder']) {
                $orderType = 'limit';
            }

            $orderBook = $this->best_market_prices($exchange, $symbol);

            $exchanges[$db->name] = [
                'exchange' => $db->name,
                'sell' => $orderBook['sell'] ?? null,
                'sell_quantity' => $orderBook['sell_quantity'],
                'buy' => $orderBook['buy'] ?? null,
                'buy_quantity' => $orderBook['buy_quantity'],
                'orderType' => $orderType,
                'comission' => $db->comission->value
            ];
        }
        return $exchanges;
    }

    public function orderBook($exchange)
    {
        $symbol = 'BTC/BRL';

        $exchange_class = "\\ccxt\\$exchange";
        $exchange = new $exchange_class([
//                'apiKey' => env("_KEY"),
//                'secret' => env("_SECRET"),
            'timeout' => 30000,
            'enableRateLimit' => true,
        ]);

        $ticker = $exchange->fetch_ticker($symbol);

        return [
            'sell' => $ticker['bid'],
            'buy' => $ticker['ask'],
            $exchange->fetch_order_book($symbol)
        ];
    }


    public function save_order($exchange, $symbol, $type = 'market', $side, $amount, $price = null)
    {

    }

    public function create_order($exchange, $symbol, $type = 'market', $side, $amount, $price = null, $params = [])
    {
        //  $symbol = 'ETH/BTC';
        //  $type = 'limit'; // or 'market', other types aren't unified yet
        //  $side = 'sell';
        //  $amount = 123.45; // your amount
        //  $price = 54.321; // your price
        // overrides
        //  $params = {
        //    'stopPrice': 123.45, // your stop price
        //    'type': 'stopLimit',
        //  }
        return $exchange->create_order($symbol, $type, $side, $amount, $price = null, $params);
    }

    public function last_trades()
    {
        return ExchangeTrade::where('side', 'sell')->orderBy('created_at', 'DESC')->take(5)->get();
    }
}
