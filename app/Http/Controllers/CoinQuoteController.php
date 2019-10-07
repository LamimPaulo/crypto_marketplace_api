<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\CoinQuoteHist;

class CoinQuoteController extends Controller
{
    public function USDTOBRL_QUOTE()
    {
        try {
            $api = new \GuzzleHttp\Client(['http_errors' => false]);

            $response = $api->get('https://api.coinbase.com/v2/exchange-rates?currency=USD');
            $statuscode = $response->getStatusCode();

            if (200 === $statuscode) {
                $result = json_decode($response->getBody()->getContents());

                $last = CoinQuoteHist::where(['coin_id' => Coin::getByAbbr("USD")->id, 'quote_coin_id' => Coin::getByAbbr("BRL")->id])->orderBy('created_at', 'DESC')->first();

                $usd_brl = CoinQuote::firstOrNew(['coin_id' => Coin::getByAbbr("USD")->id, 'quote_coin_id' => Coin::getByAbbr("BRL")->id]);
                $usd_brl->average_quote = $result->data->rates->BRL;
                $usd_brl->last_quote = $last->average_quote ?? 0;
                $usd_brl->buy_quote = $result->data->rates->BRL;
                $usd_brl->sell_quote = $result->data->rates->BRL;
                $usd_brl->save();

                CoinQuoteHist::create([
                    'coin_id' => Coin::getByAbbr("USD")->id,
                    'quote_coin_id' => Coin::getByAbbr("BRL")->id,
                    'average_quote' => $result->data->rates->BRL
                ]);

                //BRLTOUSD
                $last = CoinQuoteHist::where(['coin_id' => Coin::getByAbbr("BRL")->id, 'quote_coin_id' => Coin::getByAbbr("USD")->id])->orderBy('created_at', 'DESC')->first();

                $brl_usd = CoinQuote::firstOrNew(['coin_id' => Coin::getByAbbr("BRL")->id, 'quote_coin_id' => Coin::getByAbbr("USD")->id]);
                $brl_usd->average_quote = 1 / $result->data->rates->BRL;
                $brl_usd->last_quote = $last->average_quote ?? 0;
                $brl_usd->buy_quote = 1 / $result->data->rates->BRL;
                $brl_usd->sell_quote = 1 / $result->data->rates->BRL;
                $brl_usd->save();

                CoinQuoteHist::create([
                    'coin_id' => Coin::getByAbbr("BRL")->id,
                    'quote_coin_id' => Coin::getByAbbr("USD")->id,
                    'average_quote' => $result->data->rates->BRL
                ]);

                //LQXTOUSD
                $lqx_brl = CoinQuote::firstOrNew(['coin_id' => Coin::getByAbbr("LQX")->id, 'quote_coin_id' => Coin::getByAbbr("BRL")->id]);
                $lqx_usd = CoinQuote::firstOrNew(['coin_id' => Coin::getByAbbr("LQX")->id, 'quote_coin_id' => Coin::getByAbbr("USD")->id]);

                $lqx_usd->buy_quote = $lqx_brl->sell_quote / $result->data->rates->BRL;
                $lqx_usd->sell_quote = $lqx_brl->buy_quote / $result->data->rates->BRL;
                $lqx_usd->average_quote = ($lqx_usd->buy_quote + $lqx_usd->sell_quote) / 2;
                $lqx_usd->save();

                CoinQuoteHist::create([
                    'coin_id' => Coin::getByAbbr("LQX")->id,
                    'quote_coin_id' => Coin::getByAbbr("USD")->id,
                    'average_quote' => $lqx_usd->average_quote
                ]);


            } else {
                throw new \Exception($statuscode);
            }


        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function CRYPTO_QUOTES()
    {
        $coins = Coin::where('is_wallet', true)->where('is_crypto', true)->get();
        $api = new \GuzzleHttp\Client(['http_errors' => false]);

        try {

            foreach ($coins as $coin) {
                $response = $api->get("https://api.coinbase.com/v2/exchange-rates?currency=$coin->abbr");
                $statuscode = $response->getStatusCode();
                if (200 === $statuscode) {
                    $result = json_decode($response->getBody()->getContents());

                    //BRL
                    $quote = CoinQuote::firstOrNew(['coin_id' => $coin->id, 'quote_coin_id' => Coin::getByAbbr("BRL")->id]);
                    $last = CoinQuoteHist::where(['coin_id' => $coin->id, 'quote_coin_id' => Coin::getByAbbr("BRL")->id])->orderBy('created_at', 'DESC')->first();
                    $quote->average_quote = $result->data->rates->BRL;
                    $quote->last_quote = $last->average_quote ?? 0;
                    $quote->buy_quote = $result->data->rates->BRL + ($result->data->rates->BRL * $coin->buy_tax / 100);
                    $quote->sell_quote = $result->data->rates->BRL - ($result->data->rates->BRL * $coin->sell_tax / 100);
                    $quote->save();

                    CoinQuoteHist::create([
                        'coin_id' => $coin->id,
                        'quote_coin_id' => Coin::getByAbbr("BRL")->id,
                        'average_quote' => $result->data->rates->BRL,
                        'buy_quote' => $quote->buy_quote,
                        'sell_quote' => $quote->sell_quote
                    ]);

                    //USD
                    $quote = CoinQuote::firstOrNew(['coin_id' => $coin->id, 'quote_coin_id' => Coin::getByAbbr("USD")->id]);
                    $last = CoinQuoteHist::where(['coin_id' => $coin->id, 'quote_coin_id' => Coin::getByAbbr("USD")->id])->orderBy('created_at', 'DESC')->first();
                    $quote->average_quote = $result->data->rates->USD;
                    $quote->last_quote = $last->average_quote ?? 0;
                    $quote->buy_quote = $result->data->rates->USD + ($result->data->rates->USD * $coin->buy_tax / 100);
                    $quote->sell_quote = $result->data->rates->USD - ($result->data->rates->USD * $coin->sell_tax / 100);
                    $quote->save();

                    CoinQuoteHist::create([
                        'coin_id' => $coin->id,
                        'quote_coin_id' => Coin::getByAbbr("USD")->id,
                        'average_quote' => $result->data->rates->USD,
                        'buy_quote' => $quote->buy_quote,
                        'sell_quote' => $quote->sell_quote
                    ]);

                } else {
                    activity("Moeda {$coin->abbr} não econtrada em Coinbase");
                }
            }

        } catch
        (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function MARKETCAP_CRYPTO_QUOTES()
    {
        $coins = Coin::whereIn('abbr', ['DASH'])->where('is_crypto', true)->get();

        try {

            foreach ($coins as $coin) {

                $api = new \GuzzleHttp\Client([
                    'http_errors' => false,
                    'headers' => ['X-CMC_PRO_API_KEY' => config("services.marketcap.{$coin->abbr}.key")]
                ]);
                $response = $api->get("https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol={$coin->abbr}&convert=BRL");
                $statuscode = $response->getStatusCode();

                //BRL
                if (200 === $statuscode) {
                    $result = json_decode($response->getBody()->getContents());

                    $quote = CoinQuote::firstOrNew(['coin_id' => $coin->id, 'quote_coin_id' => Coin::getByAbbr("BRL")->id]);
                    $last = CoinQuoteHist::where(['coin_id' => $coin->id, 'quote_coin_id' => Coin::getByAbbr("BRL")->id])->orderBy('created_at', 'DESC')->first();
                    $quote->average_quote = $result->data->{$coin->abbr}->quote->BRL->price;
                    $quote->last_quote = $last->average_quote ?? 0;
                    $quote->buy_quote = $quote->average_quote + ($quote->average_quote * $coin->buy_tax / 100);
                    $quote->sell_quote = $quote->average_quote - ($quote->average_quote * $coin->sell_tax / 100);
                    $quote->save();

                    CoinQuoteHist::create([
                        'coin_id' => $coin->id,
                        'quote_coin_id' => Coin::getByAbbr("BRL")->id,
                        'average_quote' => $result->data->{$coin->abbr}->quote->BRL->price,
                        'buy_quote' => $quote->buy_quote,
                        'sell_quote' => $quote->sell_quote
                    ]);

                } else {
                    ActivityLogger::log("Moeda $coin->abbr não econtrada em Coinbase");
                }

                //USD
                $response = $api->get("https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol={$coin->abbr}");
                $statuscode = $response->getStatusCode();

                if (200 === $statuscode) {
                    $result = json_decode($response->getBody()->getContents());

                    //USD
                    $quote = CoinQuote::firstOrNew(['coin_id' => $coin->id, 'quote_coin_id' => Coin::getByAbbr("USD")->id]);
                    $last = CoinQuoteHist::where(['coin_id' => $coin->id, 'quote_coin_id' => Coin::getByAbbr("USD")->id])->orderBy('created_at', 'DESC')->first();
                    $quote->average_quote = $result->data->{$coin->abbr}->quote->USD->price;
                    $quote->last_quote = $last->average_quote ?? 0;
                    $quote->buy_quote = $quote->average_quote + ($quote->average_quote * $coin->buy_tax / 100);
                    $quote->sell_quote = $quote->average_quote - ($quote->average_quote * $coin->sell_tax / 100);
                    $quote->save();

                    CoinQuoteHist::create([
                        'coin_id' => $coin->id,
                        'quote_coin_id' => Coin::getByAbbr("USD")->id,
                        'average_quote' => $result->data->{$coin->abbr}->quote->USD->price,
                        'buy_quote' => $quote->buy_quote,
                        'sell_quote' => $quote->sell_quote
                    ]);

                } else {
                    ActivityLogger::log("Moeda $coin->abbr não econtrada em Coinbase");
                }

            }

        } catch
        (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function quotes()
    {
        $fiat = auth()->user()->country_id == 31 ? 'BRL' : 'USD';
        $fiat_coin = Coin::with('quote')->where('abbr', $fiat)->first();

        return Coin::with([
            'quote' => function ($quotes) use ($fiat_coin) {
                return $quotes->with('quote_coin')
                    ->where('buy_quote', '>', 0)
                    ->where('sell_quote', '>', 0)
                    ->where('quote_coin_id', $fiat_coin->id);
            }
        ])
            ->whereHas(
                'quote', function ($quotes) use ($fiat_coin) {
                return $quotes->where('buy_quote', '>', 0)
                    ->where('sell_quote', '>', 0)
                    ->where('quote_coin_id', $fiat_coin->id);
            }
            )
            ->where('is_active', true)
            ->where('is_wallet', true)
            ->where('is_crypto', true)
            ->get();
    }

    public function CRYPTO_TO_LQX()
    {
        $brl = Coin::getByAbbr("BRL")->id;
        $lqx = Coin::getByAbbr("LQX")->id;

        $coins = CoinQuote::with('coin')
            ->whereHas('coin', function ($coin) use ($lqx) {
                return $coin->where('is_active', true)
                    ->where('is_crypto', true)
                    ->where('is_wallet', true)
                    ->whereNotIn('id', [$lqx]);
            })
            ->where('quote_coin_id', $brl)
            ->get();

//        buy_quote
//        sell_quote

        $lqx_quote = CoinQuote::where([
            'coin_id' => $lqx,
            'quote_coin_id' => $brl,
        ])->first();

        $quotes = [];
        foreach ($coins as $coin) {

            $quotes[] = [
                'abbr' => $coin->coin->abbr,
                'quote' => [
                    'sell_quote' => sprintf("%.5f", $lqx_quote->sell_quote / $coin->buy_quote),
                    'buy_quote' => sprintf("%.5f", $coin->sell_quote / $lqx_quote->buy_quote),
                ],
            ];
        }

        return $quotes;
    }
}
