<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\NotifyLoginMail;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'recaptcha' => 'required|captcha',
            'code_2fa' => 'required'
        ]);

        try {
            $user = User::where('username', $request->username)->where('is_admin', true)->first();
            if (!$user) {
                return response(['message' => 'Usuário não encontrado.'], Response::HTTP_BAD_REQUEST);
            }

            $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();

            if ($user->is_google2fa_active) {
                if (!$g->checkCode($user->google2fa_secret, $request->code_2fa)) {
                    throw new \Exception('O código 2FA informado é inválido ou expirou. Tente novamente.');
                }
            } else {
                throw new \Exception('O código 2FA informado é inválido ou expirou. Tente novamente.');
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
                    throw new \Exception('Você deve fornecer login e senha para prosseguir. Tente Novamente.');
                }
                throw new \Exception($response['error']);
            }

            $user['ip'] = $request->ip();
            $user['created'] = Carbon::now('America/Sao_Paulo')->format('d/m/Y \à\s H:i:s');
            Localization::setLocale($user);
            Mail::to($user->email)->send(new NotifyLoginMail($user));

            return $response;
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
}
