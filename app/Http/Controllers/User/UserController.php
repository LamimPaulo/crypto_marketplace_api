<?php

namespace App\Http\Controllers\User;

use App\Enum\EnumTokenAction;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Token\TokenSmsController;
use App\Http\Requests\ConvertRequest;
use App\Http\Requests\InternationalUserRequest;
use App\Http\Requests\UserPasswordRequest;
use App\Http\Requests\UserPhoneRequest;
use App\Http\Requests\UserPinRequest;
use App\Http\Requests\UserRequest;
use App\Mail\UserCancelAccount;
use App\Models\Coin;
use App\Models\Country;
use App\Models\Funds\FundBalances;
use App\Models\LqxWithdrawal;
use App\Models\Nanotech\Nanotech;
use App\Models\System\ActivityLogger as Logger;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
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
            $user_fiat_abbr = auth()->user()->country_id === 31 ? 'BRL' : 'USD';
            $fiat_coin = Coin::getByAbbr($user_fiat_abbr);
            $wallet = UserWallet::where([
                'coin_id' => $fiat_coin->id,
                'user_id' => auth()->user()->id,
            ])->first();

            $request = new ConvertRequest();
            $request->amount = $wallet->balance;
            $request->base = $fiat_coin->abbr;
            $request->quote = 'BTC';

            $fiat_to_btc = (new OrderController(new BalanceService(), new ConversorService()))->convert($request);

            $request->quote = 'LQX';
            $fiat_to_lqx = (new OrderController(new BalanceService(), new ConversorService()))->convert($request);


            $user = User::with([
                'level' => function ($level) {
                    return $level->with('product');
                }
            ])->findOrFail(auth()->user()->id);

            $user['fiat_balance'] = $wallet->balance_rounded . ' ' . $user_fiat_abbr;
            $user['fiat_balance_'] = (float)$wallet->balance;
            $user['fiat_to_lqx'] = $fiat_to_lqx['amount'] . 'LQX';
            $user['fiat_to_btc'] = $fiat_to_btc['amount'] . 'BTC';

            return response([
                'message' => trans('messages.general.success'),
                'user' => $user,
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

            ActivityLogger::log(trans('messages.auth.pin_updated'), auth()->user()->id, User::class);

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

    public function cancel(Request $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            $user->is_canceled = true;
            $user->save();

            Mail::to($user->email)->send(new UserCancelAccount($user));

            ActivityLogger::log(trans('messages.auth.is_canceled'), auth()->user()->id, User::class);

            $user->tokens()->each(function ($token) {
                $token->delete();
            });

            return response([
                'message' => trans('messages.general.success')
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
            $nanotech_btc = $this->nanotechBTC();
            $nanotech_lqx = $this->nanotechLQX();
            $masternode = $this->nanotechMasternode();

            $products = [
                [
                    'name' => "Nanotech BTC",
                    'value_lqx' => $nanotech_btc['value_lqx'],
                    'value_brl' => $nanotech_btc['value_brl'],
                    'value_usd' => $nanotech_btc['value_usd'],
                ],
                [
                    'name' => "Nanotech LQX",
                    'value_lqx' => $nanotech_lqx['value_lqx'],
                    'value_brl' => $nanotech_lqx['value_brl'],
                    'value_usd' => $nanotech_lqx['value_usd'],
                ],
                [
                    'name' => "Masternode",
                    'value_lqx' => $masternode['value_lqx'],
                    'value_brl' => $masternode['value_brl'],
                    'value_usd' => $masternode['value_usd'],
                ],
            ];
            //total

            $products_total['value_lqx'] = 0;
            $products_total['value_brl'] = 0;
            $products_total['value_usd'] = 0;

            foreach ($products as $product) {
                $products_total['value_lqx'] += $product['value_lqx'];
                $products_total['value_brl'] += $product['value_brl'];
                $products_total['value_usd'] += $product['value_usd'];
            }

            //chart
            $total = $nanotech_btc['value_lqx'] + $nanotech_lqx['value_lqx'] + $masternode['value_brl'];

            $chart = [0, 0, 0];

            if ($total > 0) {
                $chart = [
                    (float)round($nanotech_btc['value_lqx'] * 100 / $total, 3),
                    (float)round($nanotech_lqx['value_lqx'] * 100 / $total, 3),
                    (float)round($masternode['value_brl'] * 100 / $total, 3)
                ];
            }

            return response([
                'message' => trans('messages.general.success'),
                'products' => $products,
                'product_total' => $products_total,
                'chart' => $chart
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function nanotechBTC()
    {
        $conversor = new ConversorService();
        $nanotech_btc = Nanotech::where([
            'user_id' => auth()->user()->id,
            'type_id' => 2
        ]);

        $total = $nanotech_btc->sum('amount');

        if ($total == 0) {
            return [
                'value_brl' => 0,
                'value_lqx' => 0,
                'value_usd' => 0,
            ];
        }

        $nanotech_btc_to_brl = $conversor::CRYPTO2FIAT_MIN($total, "BTC");
        $nanotech_btc_to_usd = $conversor::CRYPTO2FIAT_MIN($total, "BTC", "USD");

        return [
            'value_brl' => $nanotech_btc_to_brl['amount'],
            'value_lqx' => $conversor::FIAT2CRYPTO_MIN($nanotech_btc_to_brl['amount'], "LQX")['amount'],
            'value_usd' => $nanotech_btc_to_usd['amount']
        ];
    }

    private function nanotechLQX()
    {
        $conversor = new ConversorService();
        $nanotech_lqx = Nanotech::where([
            'user_id' => auth()->user()->id,
            'type_id' => 1
        ]);

        $total = $nanotech_lqx->sum('amount');

        if ($total == 0) {
            return [
                'value_brl' => 0,
                'value_lqx' => 0,
                'value_usd' => 0,
            ];
        }

        return [
            'value_brl' => $conversor::CRYPTO2FIAT_MIN($total, "LQX")['amount'],
            'value_lqx' => $total,
            'value_usd' => $conversor::CRYPTO2FIAT_MIN($total, "LQX", "USD")['amount'],
        ];
    }

    private function nanotechMasternode()
    {
        $conversor = new ConversorService();

        $masternode = Nanotech::where([
            'user_id' => auth()->user()->id,
            'type_id' => 3
        ]);

        $total = $masternode->sum('amount');

        if ($total == 0) {
            return [
                'value_brl' => 0,
                'value_lqx' => 0,
                'value_usd' => 0,
            ];
        }

        return [
            'value_brl' => $conversor::CRYPTO2FIAT_MIN($masternode->sum('amount'), "LQX")['amount'],
            'value_lqx' => $total,
            'value_usd' => $conversor::CRYPTO2FIAT_MIN($masternode->sum('amount'), "LQX", "USD")['amount'],
        ];
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

    public function conversion(Request $request)
    {
        $request->validate([
            'abbr' => [
                'required',
                Rule::in(['BTC', 'LQX'])
            ]
        ]);

        try {
            $quote = $request->abbr;

            $user_fiat_abbr = auth()->user()->country_id === 31 ? 'BRL' : 'USD';
            $fiat_coin = Coin::getByAbbr($user_fiat_abbr);
            $wallet = UserWallet::where([
                'coin_id' => $fiat_coin->id,
                'user_id' => auth()->user()->id,
            ])->first();

            $request = new ConvertRequest();
            $request->amount = $wallet->balance;
            $request->base = $fiat_coin->abbr;
            $request->quote = $quote;

//            if ($quote == 'LQX') {
//                $request->quote = 'LQX';
//                $fiat_to_lqx = (new OrderController(new BalanceService(), new ConversorService()))->convert($request);
//                $extra_amount = $fiat_to_lqx['amount'] * 0.1;
//
//                (new OrderController(new BalanceService(), new ConversorService()))->convertAmount($request);
//
//                $transaction_in = Transaction::create([
//                    'user_id' => auth()->user()->id,
//                    'coin_id' => Coin::getByAbbr("LQX")->id,
//                    'wallet_id' => UserWallet::where(['coin_id' => Coin::getByAbbr("LQX")->id, 'user_id' => auth()->user()->id])->first()->id,
//                    'amount' => $extra_amount,
//                    'status' => EnumTransactionsStatus::SUCCESS,
//                    'type' => EnumTransactionType::IN,
//                    'category' => EnumTransactionCategory::CONVERSION,
//                    'fee' => 0,
//                    'tax' => 0,
//                    'tx' => Uuid::uuid4()->toString(),
//                    'info' => '10% de Bônus na conversão',
//                    'error' => '',
//                    'price' => 0,
//                    'market' => 0
//                ]);
//
//                TransactionStatus::create([
//                    'status' => $transaction_in->status,
//                    'transaction_id' => $transaction_in->id,
//                ]);
//
//                BalanceService::increments($transaction_in);
//
//                return response([
//                    'message' => trans('messages.general.success'),
//                ], Response::HTTP_OK);
//            }

            (new OrderController(new BalanceService(), new ConversorService()))->convertAmount($request);

            return response([
                'message' => trans('messages.general.success'),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


}
