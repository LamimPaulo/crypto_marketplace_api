<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Masternode;
use App\Models\MasternodeHist;

class MasternodeController extends Controller
{
    public function listNodes()
    {
        return Masternode::with(['coin'])->get();
    }

    public function updateCommand()
    {
        $coins = Coin::where([
            'is_active' => true,
            'is_masternode' => true
        ])->get();

        $api = new \GuzzleHttp\Client([
            'http_errors' => false
        ]);

        try {
            Masternode::truncate();

            foreach ($coins as $coin) {
                $url = config('services.masternode.api')
                    . "coin/{$coin->abbr}?apikey="
                    . config("services.masternode.key");

                $response = $api->get($url);
                $statuscode = $response->getStatusCode();

                if (200 === $statuscode) {
                    $result = json_decode($response->getBody()->getContents());

                    $masternode = [
                        'coin_id' => $coin->id,
                        'roi' => $result->roi,
                        'daily_return' => $result->daily_returns->{strtolower($coin->abbr)},
                        'daily_return_btc' => $result->daily_returns->btc
                    ];

                    Masternode::create($masternode);
                    MasternodeHist::create($masternode);
                }
            }

            //BR /LQX
            $url = config('services.masternode.api')
                . "coin/BR?apikey="
                . config("services.masternode.key");

            $response = $api->get($url);
            $statuscode = $response->getStatusCode();

            if (200 === $statuscode) {
                $result = json_decode($response->getBody()->getContents());

                $lqx = Coin::getByAbbr('LQX');
                $brl = Coin::getByAbbr('BRL');

                $quote = CoinQuote::where([
                    'coin_id' => $lqx->id,
                    'quote_coin_id' => $brl->id,
                ])->first();

                $masternode = [
                    'coin_id' => $lqx->id,
                    'roi' => $result->roi,
                    'daily_return' => $result->daily_returns->br / $quote->average_quote,
                    'daily_return_btc' => $result->daily_returns->btc
                ];
                Masternode::create($masternode);
                MasternodeHist::create($masternode);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
