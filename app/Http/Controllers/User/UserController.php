<?php

namespace App\Http\Controllers\User;

use App\Enum\EnumTokenAction;
use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Token\TokenSmsController;
use App\Http\Requests\InternationalUserRequest;
use App\Http\Requests\UserPasswordRequest;
use App\Http\Requests\UserPhoneRequest;
use App\Http\Requests\UserPinRequest;
use App\Http\Requests\UserRequest;
use App\Models\Country;
use App\Models\Funds\FundBalances;
use App\Models\Nanotech\Nanotech;
use App\Models\System\ActivityLogger as Logger;
use App\Services\ConversorService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    protected $conversorService;

    public function __construct(ConversorService $conversor)
    {
        $this->conversorService = $conversor;
    }

    public function index()
    {
        try {
            $user = User::with('level')->findOrFail(auth()->user()->id);
            return response([
                'message' => trans('messages.general.success'),
                'user' => $user
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(UserRequest $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);

            if ($user->document) {
                return response([
                    'message' => trans('messages.auth.you_could_not_updated_your_id')
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($user->country_id !== 31) {
                return response([
                    'message' => 'The request could not be processed.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $result = $this->apiCpf($request->document);

            $user->document = $result['pessoa']['cpf'];
            $user->name = $result['pessoa']['nome'];
            $user->gender = $result['pessoa']['genero'];
            $user->mothers_name = $result['pessoa']['mae'];
            $user->birthdate = Carbon::createFromFormat('d/m/Y', $result['pessoa']['nascimento']);
            $user->document_verified = 1;
            $user->save();
            unset($user->id);

            ActivityLogger::log("Seus Dados Pessoais foram Preenchidos.", auth()->user()->id, User::class);

            return response([
                'message' => 'Dados atualizados com sucesso',
                'user' => $user
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => "Erro ao atualizar cadastro! ({$e->getMessage()})"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateInternational(InternationalUserRequest $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);

            if ($user->document) {
                return response([
                    'message' => 'You could not be fill your document again.'
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($user->country_id === 31) {
                return response([
                    'message' => 'A requisição não pode ser processada.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user->update($request->all());
            unset($user->id);

            ActivityLogger::log("Personal data updated.", auth()->user()->id, User::class);

            return response([
                'message' => 'Personal data updated.',
                'user' => $user
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => "Error updating personal data! ({$e->getMessage()})"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updatePassword(UserPasswordRequest $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            $user->password = Hash::make($request->password);
            $user->save();

            ActivityLogger::log(trans('messages.auth.password_change_success'), auth()->user()->id, User::class);

            return response([
                'message' => trans('messages.auth.password_change_success'),
                'user' => $user
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updatePin(UserPinRequest $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            $user->pin = Hash::make($request->pin);
            $user->pin_filled = true;
            $user->save();

            return response([
                'message' => trans('messages.auth.pin_updated'),
                'user' => $user
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cpf($cpf)
    {
        try {
            if (auth()->user()->document) {
                return true;
            }

            if (!$this->validateCpf($cpf)) {
                throw new \Exception('Cpf inválido.');
            }
            $result = $this->apiCpf($cpf);


            if ($result['status'] !== 'success') {
                throw new \Exception('Não foi possível verificar o cpf informado.');
            }

            $user = [
                "document" => $result['pessoa']['cpf'],
                "name" => $result['pessoa']['nome'],
                "gender" => $result['pessoa']['genero'],
                "mothers_name" => $result['pessoa']['mae'],
                "birthdate" => $result['pessoa']['nascimento'],
            ];

            return response([
                'message' => trans('messages.general.success'),
                'user' => $user
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function apiCpf($cpf)
    {
        $cpf = preg_replace("/[^0-9]/", "", $cpf);
        try {
            $api = new \GuzzleHttp\Client();

            $response = $api->get(env("NAVI_API_URL") . "/cpf/{$cpf}", [
                'headers' => [
                    'cl' => env('NAVI_API_CL'),
                    'token' => env('NAVI_API_TOKEN'),
                    'service' => 'CPF3',
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function country()
    {
        try {
            $country = Country::findOrFail(auth()->user()->country_id);
            return response([
                'message' => trans('messages.general.success'),
                'country' => $country
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public function sendPhoneCode(UserPhoneRequest $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            $user->phone = $request->phone;
            $user->phone_verified_at = null;
            $user->save();

            $request['action'] = EnumTokenAction::PHONE_VERIFY;
            return TokenSmsController::generate($request);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function verifyPhoneCode(Request $request)
    {
        try {
            $request['action'] = EnumTokenAction::PHONE_VERIFY;
            $response = TokenSmsController::verify($request);
            if (!$response) {
                throw new \Exception(trans('messages.auth.invalid_code'));
            }

            $user = User::findOrFail(auth()->user()->id);
            $user->phone_verified_at = Carbon::now();
            $user->save();

            ActivityLogger::log(trans('messages.auth.telephone_number_verified', ['number' => $user->phone]), auth()->user()->id, User::class);

            return response([
                'message' => trans('messages.auth.telephone_verified')
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function hist()
    {
        try {
            $hist = Logger::where('causer_id', auth()->user()->id)
                ->orWhere('causer_id', auth()->user()->id)
                ->orderBy('id', 'DESC')->take(10)
                ->get()->makeHidden('properties');


            return response([
                'message' => trans('messages.general.success'),
                'logs' => $hist
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function dashboard()
    {
        try {
            $conversor = new ConversorService();
            //nanotech btc
            $nanotech_btc = Nanotech::where([
                'user_id' => auth()->user()->id,
                'type_id' => 1
            ]);

            $nanotech_btc_to_brl = $conversor::CRYPTO2FIAT_MIN($nanotech_btc->sum('amount'), "BTC");
            $nanotech_brl_to_lqx = $conversor::FIAT2CRYPTO_MIN($nanotech_btc_to_brl['amount'], "LQX");

            //nanotech qlx
            $nanotech_lqx = Nanotech::where([
                'user_id' => auth()->user()->id,
                'type_id' => 2
            ]);

            $nanotech_lqx_to_brl = $conversor::CRYPTO2FIAT_MIN($nanotech_lqx->sum('amount'), "LQX");

            //masternode
            $masternode = Nanotech::where([
                'user_id' => auth()->user()->id,
                'type_id' => 3
            ]);

            $masternode_to_brl = $conversor::CRYPTO2FIAT_MIN($masternode->sum('amount'), "LQX");

            $products = [
                [
                    'name' => "Nanotech BTC",
                    'value_lqx' => $nanotech_brl_to_lqx['amount'],
                    'value_brl' => $nanotech_btc_to_brl['amount'],
                ],
                [
                    'name' => "Nanotech LQX",
                    'value_lqx' => $nanotech_lqx->sum('amount'),
                    'value_brl' => $nanotech_lqx_to_brl['amount'],
                ],
                [
                    'name' => "Masternode",
                    'value_lqx' => $masternode->sum('amount'),
                    'value_brl' => $masternode_to_brl['amount'],
                ],
            ];

            //total
            $products_total['value_lqx'] = 0;
            $products_total['value_brl'] = 0;

            foreach ($products as $product) {
                $products_total['value_lqx'] += $product['value_lqx'];
                $products_total['value_brl'] += $product['value_brl'];
            }

            //chart
            $total = $nanotech_brl_to_lqx['amount'] + $nanotech_lqx->sum('amount') + $nanotech_lqx->sum('amount');

            return response([
                'message' => trans('messages.general.success'),
                'products' => $products,
                'product_total' => $products_total,
                'chart' => [
                    (float)round($nanotech_brl_to_lqx['amount'] * 100 / $total,3),
                    (float)round($nanotech_lqx->sum('amount') * 100 / $total,3),
                    (float)round($masternode->sum('amount') * 100 / $total,3)
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function funds()
    {
        $funds = FundBalances::with(['fund'])->where('user_id', auth()->user()->id)->get();
        $sum = 0;

        return $funds;
    }

    /**
     * @param $cpf
     * @return bool
     */
    private function validateCpf($cpf)
    {
        if (empty($cpf)) {
            return false;
        }

        $cpf = preg_replace("/[^0-9]/", "", $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        if (strlen($cpf) != 11) {
            return false;
        } else if ($cpf == '00000000000' ||
            $cpf == '11111111111' ||
            $cpf == '22222222222' ||
            $cpf == '33333333333' ||
            $cpf == '44444444444' ||
            $cpf == '55555555555' ||
            $cpf == '66666666666' ||
            $cpf == '77777777777' ||
            $cpf == '88888888888' ||
            $cpf == '99999999999') {
            return false;
        } else {
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            return true;
        }
    }
}
