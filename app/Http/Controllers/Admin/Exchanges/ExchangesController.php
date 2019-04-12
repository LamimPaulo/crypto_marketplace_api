<?php

namespace App\Http\Controllers\Admin\Exchanges;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoinProviderEndpointRequest;
use App\Http\Requests\CoinProviderRequest;
use App\Models\Coin;
use App\Models\CoinProvider;
use App\Models\CoinProviderEndpoint;
use App\Models\Exchange\Exchanges;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExchangesController extends Controller
{
    public function assets()
    {
        try {
            $exchanges = CoinProvider::paginate(10);
            return response($exchanges, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function arbitrage()
    {
        try {
            $exchanges = Exchanges::paginate(10);
            return response($exchanges, Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function assetProvider($provider)
    {
        try {
            $exchange = CoinProvider::with('endpoints')->findOrFail($provider);

            $api = new \GuzzleHttp\Client();

            $timestamp = Carbon::now()->timestamp * 1000;

            $queryString = "timestamp=" . $timestamp;

            $signature = $this->signOperations($queryString);

            $endpoint_account = $exchange->endpoints->where('name', 'account')->first();

            $response = $api->{$endpoint_account->method}("{$exchange->endpoint}{$endpoint_account->endpoint}?$queryString&signature=$signature", [
                'headers' => [
                    'X-MBX-APIKEY' => config("services.{$exchange->service_key}.key"),
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $coins = [];
            foreach ($result['balances'] as $balance) {
                if ($balance['free'] > 0) {
                    $coin = Coin::firstOrNew(['abbr' => $balance['asset']]);
                    $coin->free = $balance['free'];
                    $coin->locked = $balance['locked'];
                    $coin->asset = $balance['asset'];
                    array_push($coins, $coin);
                }
            }

            return response([
                'exchange' => $exchange,
                'balances' => collect($coins)->sortByDesc('free'),
            ], Response::HTTP_OK);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new \Exception($e->getResponse());
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function assetsUpdate(CoinProviderRequest $request)
    {
        try {
            $exchange = CoinProvider::findOrFail($request->id);
            $exchange->update($request->all());

            return response([
                'status' => 'success',
                'message' => 'Exchange Atualizada com sucesso'
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function assetsEnpointsUpdate(CoinProviderEndpointRequest $request)
    {
        try {
            foreach($request->endpoints as $end){
                $end_ = CoinProviderEndpoint::find($end['id']);
                if($end_){
                    $end_->update($end);
                }else{
                    CoinProviderEndpoint::create($end);
                }
            }

            return response([
                'status' => 'success',
                'message' => 'Endpoints Atualizados com sucesso'
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function assetsEndpointDelete(Request $request)
    {
        try {
            $endpoint = CoinProviderEndpoint::with('parameters')->findOrFail($request->id);

            foreach ($endpoint->parameters as $param){
                $param->delete();
            }
            $endpoint->delete();

            return response([
                'status' => 'success',
                'message' => 'Endpoint removido com sucesso'
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    private function signOperations($string)
    {
        return hash_hmac('sha256', $string, config('services.binance.secret'));
    }
}
