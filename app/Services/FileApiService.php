<?php

namespace App\Services;

use GuzzleHttp\Client;
use Zttp\Zttp;

class FileApiService
{
    private static function url($url)
    {
        return vsprintf('%s/%s', [
            env("FILE_API_URL"),
            ltrim($url, '/'),
        ]);
    }

    private static function headers()
    {
        return [
            'client' => env('NAVI_API_CL'),
            'Authorization' => env('NAVI_API_TOKEN')
        ];
    }

    public static function files($page = 1)
    {
        $endpoint = "/files?page=$page";

        try {
            $response = Zttp::withHeaders(self::headers())
                ->get(self::url($endpoint));

            $result = $response->json();

            if (!$response->isOk()) {
                throw new \Exception($result['message']);
            }

            return $result;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function storeFile($file, $subfolder = null, $visibility = 'private')
    {
        $endpoint = "/files/store";

        try {
            $client = new Client();
            $response = $client->post(
                self::url($endpoint), [
                    'headers' => self::headers(),
                    'multipart' => [
                        [
                            'name' => 'subfolder',
                            'contents' => $subfolder
                        ],
                        [
                            'name' => 'visibility',
                            'contents' => $visibility
                        ],
                        [
                            'name' => 'file',
                            'contents' => fopen($file->getPathName(), 'r')
                        ],

                    ],
                ]
            );

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function updateFile($file_id, $visibility = 'public')
    {
        $endpoint = "/files/update";

        try {
            $response = Zttp::withHeaders(self::headers())
                ->post(self::url($endpoint), [
                    'file' => $file_id,
                    'visibility' => $visibility,
                ]);

            $result = $response->json();

            if (!$response->isOk()) {
                throw new \Exception($result['message']);
            }

            return $result;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getFile($file_id, $visibility = 'private')
    {

        try {
            if ($visibility == 'private') {
                $response = Zttp::withHeaders(self::headers())->get(self::url("/files/private/$file_id"));
            } else {
                $response = Zttp::get(self::url("/files/public/$file_id"));
            }

            $result = $response->json();

            if (!$response->isOk()) {
                throw new \Exception($result['message']);
            }

            return $result;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
