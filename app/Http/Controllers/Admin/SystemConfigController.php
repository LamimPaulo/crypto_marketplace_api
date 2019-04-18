<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SysConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
            $config = SysConfig::findOrFail($request->id);
            if($config->investiment_return!=$request->investiment_return){
                Artisan::call('nanotech:percentages');
            }
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
