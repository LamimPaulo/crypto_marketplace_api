<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumWeekdays;
use App\Http\Controllers\Controller;
use App\Models\SysConfig;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemConfigController extends Controller
{
    public function index()
    {
        try {
            $config = SysConfig::first();
            return response($config, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request)
    {
        try {
            $days = $request->withdrawalDaysArr;
            $wdays = [];

            foreach ($days as $d) {
                array_push($wdays, EnumWeekdays::NUM[$d]);
            }

            $request['withdrawal_days'] = implode(",", $wdays);

            $config = SysConfig::findOrFail($request->id);
            $config->update($request->all());

            return response([
                'status' => 'success',
                'message' => 'Configurações atualizadas com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
