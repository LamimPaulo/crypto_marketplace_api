<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;

class OffScreenController extends Controller
{

    public static function post($type, $data = "", $coin)
    {

        try {
            set_time_limit(300);
            $result = (new Client())->post(config("services.offscreen.{$coin}"), [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    encrypt([
                        'data' => $data,
                        'type' => $type
                    ])
                ]
            ]);

            $response = $result->getBody()->getContents();
            $response = decrypt($response);

            if (!isset($response['error'])) {
                return $response;
            } else {
                throw new \Exception($response['error']);
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . ' => ' . $ex->getLine() . ' => ' . $ex->getFile());
        }
    }

}
