<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumWeekdays;
use App\Http\Controllers\Controller;
use App\Models\SupportConfig;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SupportConfigController extends Controller
{
    public function index()
    {
        try {
            $config = SupportConfig::first();
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
            $config = SupportConfig::findOrFail($request->id);
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
