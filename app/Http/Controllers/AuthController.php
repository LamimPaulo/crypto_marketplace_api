<?php

namespace App\Http\Controllers;

use App\Enum\EnumOperationType;
use App\Helpers\Localization;
use App\Mail\NotifyLoginMail;
use App\Mail\UnderAnalysisMail;
use App\Mail\VerifyMail;
use App\Models\Coin;
use App\Models\Country;
use App\Models\User\UserWallet;
use App\User;
use App\VerifyUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'code_2fa' => 'nullable|numeric',
            'recaptcha' => 'required|captcha',
        ]);

        try {

            $user = User::where('email', $request->username)
                ->orWhere('phone', $request->username)
                ->orWhere('username', $request->username)->first();

            if (!$user) {
                throw new \Exception('Ops, usuário não encontrado!');
            }

            if ($user->is_under_analysis) {
                Mail::to($user->email)->send(new UnderAnalysisMail($user));
                throw new \Exception('Uma tentativa de acesso em sua conta não foi autorizada, por Medida de segurança ela foi bloqueada, por favor acesse o <a href="https://www.facebook.com/liquidex/" target="_blank"> suporte aqui</a>, identifique-se e peça que seja desbloqueada.');
            }

            if ($user->is_canceled) {
                throw new \Exception('Conta cancelada. Não é possível acessar a plataforma.');
            }

            if (!isset($user->email_verified_at)) {
                Localization::setLocale($user);
                Mail::to($user->email)->send(new VerifyMail($user));
                throw new \Exception('Você deve confirmar sua conta. Enviamos um email de verificação, favor verificar sua caixa de entrada.');
            }

            if ($user->is_google2fa_active) {

                if (is_null($request->code_2fa)) {
                    throw new \Exception('Você deve informar o código 2FA para obter acesso à plataforma. Tente novamente.');
                }

                $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();

                if (!$g->checkCode($user->google2fa_secret, $request->code_2fa)) {
                    throw new \Exception('O código 2FA informado é inválido ou expirou. Tente novamente.');
                }
            }

            $user->tokens->each(function ($token) {
                $token->delete();
            });

            $req = Request::create('/oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => config('services.passport.client_id'),
                'client_secret' => config('services.passport.client_secret'),
                'username' => $user->email,
                'password' => $request->password,
            ]);
            $res = app()->handle($req);
            $responseBody = $res->getContent();
            $response = json_decode($responseBody, true);

            if (isset($response['error'])) {
                if ($response['error'] === 'invalid_credentials') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }

                if ($response['error'] === 'invalid_request') {
                    throw new \Exception('Dados Inválidos. Tente Novamente.');
                }
                throw new \Exception($response['error']);
            }

            $this->checkWallets($user);

            $user['ip'] = $request->ip();
            $user['created'] = Carbon::now('America/Sao_Paulo')->format('d/m/Y \à\s H:i:s');
            $user['agent'] = $request->header('User-Agent');
            Localization::setLocale($user);
            Mail::to($user->email)->send(new NotifyLoginMail($user));

            return $response;
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|min:4|max:20|unique:users,username|regex:/(^[A-Za-z0-9_]+$)+/',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'phone' => 'required|numeric|min:8|unique:users',
            'recaptcha' => 'required|captcha',
        ], [
            'username.required' => 'O username é obrigatório.',
            'username.regex' => 'O username só pode conter letras Maíusculas, mínusculas, números e _.',
            'username.unique' => 'O username já está em uso, por favor escolha um diferente.',
            'phone.required' => 'O número de celular é obrigatório.',
            'phone.numeric' => 'O número de celular deve conter somente números e o ddd deve ser informado.',
            'phone.unique' => 'O telefone informado já está em uso.',
            'email.required' => 'Você deve informar um email.',
            'email.email' => 'Você deve informar um email válido.',
            'email.unique' => 'O email informado já está em uso.',
            'password.min' => 'A senha deve conter um mínimo de 6 caracteres.',
            'password.regex' => 'A senha deve conter ao menos uma letra Maíuscula, uma letra mínuscula, um número e um caracter especial.',
            'password.confirmed' => 'A confirmação deve corresponder com a senha.'
        ]);

        try {
            $country = Country::where('code', $request->countryCode)->first();

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'country_id' => $country->id,
                'user_level_id' => $country->id === 31 ? 1 : 7,
                'password' => Hash::make($request->password),
            ]);

            VerifyUser::create([
                'user_id' => $user->id,
                'token' => Uuid::uuid4()->toString()
            ]);

            Localization::setLocale($user);
            Mail::to($user->email)->send(new VerifyMail($user));

            return response(['message' => 'Sua Conta foi criada com sucesso, enviamos um de confirmação para você.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout()
    {
        try {
            auth()->user()->tokens->each(function ($token, $key) {
                $token->delete();
            });
            return response(['message' => 'Você deslogou com sucesso!'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => "Erro na requisição. [{$e->getMessage()}]"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function verifyUser($token)
    {
        $verifyUser = VerifyUser::where('token', $token)->first();
        if (isset($verifyUser)) {
            $user = $verifyUser->user;
            if (!$user->email_verified_at) {
                try {
                    DB::beginTransaction();
                    $verifyUser->user->email_verified_at = Carbon::now();
                    $verifyUser->user->save();
                    $this->checkWallets($user);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    abort(403, $e->getMessage());
                }
            }
        } else {
            abort(403, 'Não autorizado.');
        }

        return redirect(env('FRONT_URL') . '/register/verify');

    }

    public function checkWallets($user)
    {
        if ($user->country_id != 31) {
            $usd_wallet = UserWallet::with('coin')
                ->whereHas('coin', function ($coin) {
                    return $coin->where('abbr', 'LIKE', 'USD');
                })
                ->where(['user_id' => $user->id, 'is_active' => 1])->first();

            if (!$usd_wallet) {
                $uuid4 = Uuid::uuid4();
                UserWallet::create([
                    'user_id' => $user->id,
                    'coin_id' => Coin::getByAbbr('USD')->id,
                    'address' => $uuid4->toString(),
                    'balance' => 0
                ]);
            }
        }

        if ($user->country_id == 31) {
            $brl_wallet = UserWallet::with('coin')
                ->whereHas('coin', function ($coin) {
                    return $coin->where('abbr', 'LIKE', 'BRL');
                })
                ->where(['user_id' => $user->id, 'is_active' => 1])->first();

            if (!$brl_wallet) {
                $uuid4 = Uuid::uuid4();
                UserWallet::create([
                    'user_id' => $user->id,
                    'coin_id' => Coin::getByAbbr('BRL')->id,
                    'address' => $uuid4->toString(),
                    'balance' => 0
                ]);
            }
        }

        $lqx_wallet = UserWallet::with('coin')
            ->whereHas('coin', function ($coin) {
                return $coin->where('abbr', 'LIKE', 'LQX');
            })
            ->where(['user_id' => $user->id, 'is_active' => 1])->first();

        if (!$lqx_wallet) {
            $uuid4 = Uuid::uuid4();
            UserWallet::create([
                'user_id' => $user->id,
                'coin_id' => Coin::getByAbbr('LQX')->id,
                'address' => $uuid4->toString(),
                'balance' => 0
            ]);
        }

        $coins = Coin::whereNotIn('abbr', ['BRL', 'USD', 'LQX'])->where([
            'is_wallet' => true,
            'is_active' => true,
            'core_status' => true
        ])->get();

        foreach ($coins as $loop_coin) {
            $wallet = UserWallet::with('coin')
                ->whereHas('coin', function ($coin) use ($loop_coin) {
                    return $coin->where('id', $loop_coin->id);
                })
                ->where(['user_id' => $user->id, 'is_active' => 1])->first();

            if (!$wallet) {

                $uuid4 = Uuid::uuid4();
                $address = env('APP_ENV') == 'local' ? $uuid4->toString() : OffScreenController::post(EnumOperationType::CREATE_ADDRESS, NULL, $loop_coin->abbr);

                UserWallet::create([
                    'user_id' => $user->id,
                    'coin_id' => $loop_coin->id,
                    'address' => str_replace('bitcoincash:', '', $address),
                    'balance' => 0
                ]);
            }
        }
    }

}
