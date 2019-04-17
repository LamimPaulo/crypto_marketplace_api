<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\SystemAccount;
use Symfony\Component\HttpFoundation\Response;

class SystemAccountController extends Controller
{
    public function index()
    {

        $accounts = SystemAccount::with(['bank'])->where('is_active', 1);

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
