<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\WithdrawalDeadline;
use App\Models\System\WithdrawalHolyday;
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
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public function holydays()
    {
        try {
            $days = WithdrawalHolyday::orderBy('day', 'DESC')->paginate(10);
            return response($days, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'taxes' => []
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function storeHolydays(Request $request)
    {
        $request->validate([
            'days.*.day' => 'required|date_format:Y-m-d|unique:withdrawal_holydays,day',
        ], [
            'days.*.day.required' => 'O dia é obrigatório.',
            'days.*.day.date_format' => 'O dia deve ter um formato válido (Y-m-d)',
            'days.*.day.unique' => 'Os Feriados devem ser distintos (sem datas duplicadas)',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->days as $day) {
                WithdrawalHolyday::create([
                    'info' => $day['info'],
                    'day' => $day['day']
                ]);
            }

            DB::commit();

            $days = WithdrawalHolyday::orderBy('day', 'DESC')->paginate(10);
            return response([
                'message' => 'Salvo com sucesso!',
                $days
                ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public function deleteHolyday(Request $request)
    {
        $request->validate([
            'day' => 'required|date_format:Y-m-d',
        ], [
            'day.required' => 'O dia é obrigatório.',
            'day.date_format' => 'O dia deve ter um formato válido (Y-m-d)',
        ]);

        try {
            DB::beginTransaction();

            $day = WithdrawalHolyday::where([
                    'day' => $request->day
                ])->first();

            $day->delete();

            DB::commit();

            $days = WithdrawalHolyday::orderBy('day', 'DESC')->paginate(10);
            return response([
                'message' => 'Excluído com sucesso!',
                $days
                ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

}
