<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\WithdrawalDeadline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class WithdrawalDeadlineController extends Controller
{
    public function index()
    {
        try {

            $taxes = WithdrawalDeadline::all();
            return response($taxes, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'taxes' => []
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'taxes.*.tax' => 'required|numeric',
            'taxes.*.deadline' => 'required|numeric'
        ], [
            'taxes.*.tax.required' => 'A Taxa é obrigatória.',
            'taxes.*.deadline.required' => 'O Prazo é obrigatório',
            'taxes.*.tax.numeric' => 'A Taxa deve ser informada de forma válida (apenas números e pontos)',
            'taxes.*.deadline.numeric' => 'O Prazo deve ser informado de forma válida (apenas números e pontos)',
        ]);

        try {
            DB::beginTransaction();

            WithdrawalDeadline::truncate();

            foreach ($request->taxes as $tax) {
                WithdrawalDeadline::create([
                    'deadline' => $tax['deadline'],
                    'tax' => $tax['tax'],
                    'status' => $tax['status']
                ]);
            }
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }


    }
}
