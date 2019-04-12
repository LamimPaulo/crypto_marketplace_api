<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\User;
use Illuminate\Http\Request;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;
use Symfony\Component\HttpFoundation\Response;

class Google2faController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new GoogleAuthenticator();
    }

    public function getQrCodeUrl()
    {
        try {
            $user = User::findOrFail(auth()->user()->id);

            if (auth()->user()->is_google2fa_active) {
                return response([
                    'status' => 1,
                    'message' => trans('messages.2fa.already_activated')
                ], Response::HTTP_OK);
            }

            $secret = $this->google2fa->generateSecret();

            $qrCode = GoogleQrUrl::generate($user->username, $secret, env('APP_NAME'));

            return response([
                'status' => 0,
                'message' => trans('messages.general.success'),
                'secret' => $secret,
                'qr_code' => $qrCode,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function activate2fa(Request $request)
    {
        $request->validate([
            'secret' => 'required|unique:users,google2fa_secret',
            'code' => 'required|numeric'
        ], [
            'secret.unique' => trans('messages.2fa.invalid_secret')
        ]);

        try {
            if (!auth()->user()->is_google2fa_active) {

                $user = User::findOrFail(auth()->user()->id);

                if ($this->google2fa->checkCode($request->secret, $request->code)) {
                    $user->google2fa_secret = $request->secret;
                    $user->is_google2fa_active = true;
                    $user->save();

                    ActivityLogger::log(trans('messages.2fa.activated'), $user->id);

                    return response([
                        'message' => trans('messages.2fa.activated'),
                        'user' => $user
                    ], Response::HTTP_OK);

                } else {
                    throw new \Exception(trans('messages.2fa.invalid_code'));
                }
            }

            throw new \Exception(trans('messages.2fa.already_activated'));

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public function deactivate2fa()
    {
        try {
            if (auth()->user()->is_google2fa_active) {

                $user = User::findOrFail(auth()->user()->id);

                $user->google2fa_secret = null;
                $user->is_google2fa_active = false;
                $user->save();

                ActivityLogger::log(trans('messages.2fa.deactivated'), $user->id);

                return response([
                    'message' => trans('messages.2fa.deactivated'),
                    'user' => $user
                ], Response::HTTP_OK);

            }

            throw new \Exception(trans('messages.2fa.not_activated'));

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }
}
