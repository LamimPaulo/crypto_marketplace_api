<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAddressRequest;
use App\User;
use Symfony\Component\HttpFoundation\Response;


class UserAddressController extends Controller
{
    public function show($zip)
    {
        try {
            $response = $this->apiCep($zip);

            $address = [
                'state' => $response['endereco']['estado'],
                'city' => $response['endereco']['cidade'],
                'district' => $response['endereco']['bairro'],
                'address' => $response['endereco']['tp_logradouro'] . $response['endereco']['logradouro']

            ];

            return response([
                'message' => trans('messages.general.success'),
                'address' =>$address
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(UserAddressRequest $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            $user->fill($request->only('zip_code', 'state', 'city', 'district', 'address', 'number', 'complement'));
            $user->save();

            return response([
                'message' => "Endereço Atualizado com sucesso!",
                'user' => $user,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function apiCep($cep)
    {
        $cep = preg_replace("/[^0-9]/", "", $cep);
        try {
            $api = new \GuzzleHttp\Client();

            $response = $api->get(env("NAVI_API_URL") . "/cep/{$cep}", [
               'headers' => [
                   'cl' => env('NAVI_API_CL'),
                   'token' => env('NAVI_API_TOKEN'),
                   'service' => 'CEP'
               ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}