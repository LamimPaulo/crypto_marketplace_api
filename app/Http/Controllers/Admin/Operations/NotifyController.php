<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Models\Coin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class NotifyController extends Controller
{
    public function notify($abbr, $txid)
    {
        try {
            $api = new \GuzzleHttp\Client();

            $url = str_replace("operation", "notify", config("services.offscreen.{$abbr}"));
            $response = $api->get($url . '/' . $txid);

            $result = json_decode($response->getBody()->getContents());

            return response([
                'message' => 'Notificado',
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $result = json_decode($response->getBody()->getContents());
            return response([
                'message' => $result->message,
                'status' => 'error'
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage(),
                'status' => 'error'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function coins()
    {
        return Coin::where([
            'is_active' => true,
            'is_wallet' => true,
            'is_crypto' => true,
        ])->get();
    }
}
