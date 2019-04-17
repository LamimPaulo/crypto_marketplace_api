<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\SystemAccount;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemAccountController extends Controller
{

    public function index()
    {
        try {
            $coins = SystemAccount::paginate(10);
            return response($coins, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            SystemAccount::create($request->all());

            return response([
                'status' => 'success',
                'message' => 'Conta adicionada com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request)
    {
        try {
            $account = SystemAccount::findOrFail($request->id);

            $account->update($request->all());

            return response([
                'status' => 'success',
                'message' => 'Conta atualiza com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
