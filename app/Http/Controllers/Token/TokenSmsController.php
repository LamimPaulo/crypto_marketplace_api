<?php

namespace App\Http\Controllers\Token;

use App\Enum\EnumTokenAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenSmsController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public static function generate(Request $request)
    {
        $request->validate([
            'action' => 'required|numeric'
        ]);

        try {
            $api = new \GuzzleHttp\Client();

            $response = $api->post(env("NAVI_API_URL") . "/token/sms/gerar", [
                'headers' => [
                    'cl' => env('NAVI_API_CL'),
                    'token' => env('NAVI_API_TOKEN'),
                    'service' => 'TKSMS',
                ],
                'form_params' => [
                    'nome' => auth()->user()->name,
                    'numero' => '55' . $request->phone,
                    'sistema' => env('APP_NAME') . ' - ' . EnumTokenAction::ACTION[$request->action],
                ]
            ]);

            $result = json_decode($response->getBody()->getContents());

            return response([
                'message' => $result->message,
                'status' => 'success'
            ], Response::HTTP_CREATED);
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
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }
    }

    public static function verify(Request $request)
    {
        $request->validate([
            'action' => 'required|numeric',
            'code' => 'required'
        ]);

        try {
            $api = new \GuzzleHttp\Client();

            $response = $api->post(env("NAVI_API_URL") . "/token/sms/validar", [
                'headers' => [
                    'cl' => env('NAVI_API_CL'),
                    'token' => env('NAVI_API_TOKEN'),
                    'service' => 'TKVERIFY',
                ],
                'form_params' => [
                    'numero' => '55' . auth()->user()->phone,
                    'codigo' => $request->code,
                    'sistema' => env('APP_NAME') . ' - ' . EnumTokenAction::ACTION[$request->action],
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($result['status'] == 'success') {
                return true;
            }
            return false;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return false;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
