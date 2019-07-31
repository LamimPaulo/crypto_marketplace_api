<?php

namespace App\Http\Controllers;

use App\Enum\EnumOperationType;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Services\ConversorService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $address = OffScreenController::post(EnumOperationType::CREATE_ADDRESS, null, $request->abbr);
            return $address;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function lqx()
    {
        $lqx = Coin::getByAbbr("LQX")->id;
        $usd = Coin::getByAbbr("USD")->id;
        $btc = Coin::getByAbbr("BTC")->id;

        $lqx_usd = CoinQuote::where([
            'coin_id' => $lqx,
            'quote_coin_id' => $usd
        ])->first()->average_quote;

        $btc_usd = CoinQuote::where([
            'coin_id' => $btc,
            'quote_coin_id' => $usd
        ])->first()->average_quote;

        $btc_lqx = ConversorService::FIAT2CRYPTO_MAX($lqx_usd, "BTC", "USD")['amount'];


        $api = new \GuzzleHttp\Client(['http_errors' => false]);

        $response = $api->get('https://www.dashcentral.org/api/v1/public');
        $statuscode = $response->getStatusCode();

        if (200 !== $statuscode) {
            return
                [
                    "general" => [
                        "consensus_blockheight" => 0,
                        "consensus_version" => 0,
                        "consensus_protocolversion" => 0,
                        "consensus_masternodes" => 0,
                        "all_user" => 0,
                        "registered_masternodes" => 0,
                        "registered_masternodes_verified" => 0
                    ],
                    "exchange_rates" => [
                        "dash_usd" => sprintf("%.8f", $lqx_usd),
                        "btc_usd" => sprintf("%.8f", $btc_usd),
                        "btc_dash" => sprintf("%.8f", $btc_lqx),
                    ],
                ];
        }

        $result = json_decode($response->getBody()->getContents(), 1);

        return
            [
                "general" => [
                    "consensus_blockheight" => $result['general']['consensus_blockheight'],
                    "consensus_version" => $result['general']['consensus_version'],
                    "consensus_protocolversion" => $result['general']['consensus_protocolversion'],
                    "consensus_masternodes" => $result['general']['consensus_masternodes'],
                    "all_user" => $result['general']['all_user'],
                    "registered_masternodes" => $result['general']['registered_masternodes'],
                    "registered_masternodes_verified" => $result['general']['registered_masternodes_verified'],
                ],
                "exchange_rates" => [
                    "dash_usd" => sprintf("%.8f", $lqx_usd),
                    "btc_usd" => sprintf("%.8f", $btc_usd),
                    "btc_dash" => sprintf("%.8f", $btc_lqx),
                ],
            ];
    }
}