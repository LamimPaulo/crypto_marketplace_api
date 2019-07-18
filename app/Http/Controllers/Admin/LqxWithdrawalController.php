<?php

namespace App\Http\Controllers\Admin;

use App\Models\LqxWithdrawal;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LqxWithdrawalController extends Controller
{
    public function index()
    {
        try {
            return response([
                'status' => 'success',
                'message' => '',
                'dates' => LqxWithdrawal::orderBy('date')->get()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'percent' => 'required|numeric',
            'date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();

            $curdate = Carbon::now()->addDay();
            $date = Carbon::parse($request->date);

            if($curdate->gte($date)){
                throw new \Exception("Esta data de retirada não pode ser modificada.");
            }

            $date = LqxWithdrawal::where([
                'is_executed' => false,
                'id' => $request->id
            ])->firstOrFail();

            if (!$date) {
                throw new \Exception("Esta data de retirada não pode ser modificada.");
            }

            $date->date = $request->date;
            $date->percent = $request->percent;
            $date->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => ''
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


}
