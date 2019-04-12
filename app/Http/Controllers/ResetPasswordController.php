<?php

namespace App\Http\Controllers;

use App\Helpers\Localization;
use App\Mail\ResetPassword;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordController extends Controller
{
    public function sendEmail(Request $request)
    {
        if (!$this->validateEmail($request->email)) {
            return $this->failedResponse();
        }
        $this->send($request->email);
        return $this->successResponse();
    }

    public function send($email)
    {
        $user = User::where('email',$email)->first();
        $token = $this->createToken($email);
        Localization::setLocale($user);
        Mail::to($email)->send(new ResetPassword($token, $user));
    }

    public function createToken($email)
    {
        $oldToken = DB::table('password_resets')->where('email', $email)->first();
        if ($oldToken) {
            return $oldToken->token;
        }
        $token = str_random(60);
        $this->saveToken($token, $email);
        return $token;
    }

    public function saveToken($token, $email)
    {
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    public function validateEmail($email)
    {
        return !!User::where('email', $email)->first();
    }

    public function failedResponse()
    {
        return response([
            'message' => trans('messages.auth.email_not_found')
        ], Response::HTTP_NOT_FOUND);
    }

    public function successResponse()
    {
        return response([
            'message' => trans('messages.auth.email_sent')
        ], Response::HTTP_OK);
    }

//
    public function process(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
        ], [
            'email.required' => 'Você deve informar um email.',
            'email.email' => 'Você deve informar um email válido.',
            'password.min' => 'A senha deve conter um mínimo de 6 caracteres.',
            'password.regex' => 'A senha deve conter ao menos uma letra Maíuscula, uma letra mínuscula, um número e um caracter especial.',
            'password.confirmed' => 'A confirmação deve corresponder com a senha.'
        ]);

        return $this->getPasswordResetTableRow($request)->count() > 0 ? $this->changePassword($request) : $this->tokenNotFoundResponse();
    }

    private function getPasswordResetTableRow($request)
    {
        return DB::table('password_resets')->where(['email' => $request->email, 'token' => $request->token]);
    }

    private function tokenNotFoundResponse()
    {
        return response([
            'message' => trans('messages.auth.token_email_invalid')
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function changePassword($request)
    {
        $user = User::whereEmail($request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);
        $this->getPasswordResetTableRow($request)->delete();
        return response([
            'message' => trans('messages.auth.password_change_success')
        ], Response::HTTP_CREATED);
    }
}
