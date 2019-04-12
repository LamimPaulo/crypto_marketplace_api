<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\SystemAccount;
use Symfony\Component\HttpFoundation\Response;

class SystemAccountController extends Controller
{
    public function index()
    {

        if(auth()->user()->country_id===31){
            $accounts = SystemAccount::with(['bank', 'provider'])->where('is_active', 1);
        }else{
            $accounts = SystemAccount::with(['bank', 'provider'])->where('provider_id','>',1)->where('is_active', 1);
        }


        if ($accounts->count() > 0) {
            return response([
                'message' => trans('messages.general.success'),
                'count' => $accounts->count(),
                'accounts' => $accounts->get()
            ], Response::HTTP_OK);
        }

        return response([
            'message' => 'Nenhuma Conta encontrada',
            'count' => $accounts->count(),
            'accounts' => $accounts->get()
        ], Response::HTTP_NOT_FOUND);
    }
}
