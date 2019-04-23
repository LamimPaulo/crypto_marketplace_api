<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nanotech\NanotechType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class NanotechTypeController extends Controller
{

    public function index()
    {
        return NanotechType::all();
    }

    public function update(Request $request)
    {
        try {
            foreach ($request->all() as $req) {
                $nanotech = NanotechType::findOrFail($req['id']);
                $nanotech->montly_return = $req['montly_return'];
                $nanotech->save();
            };

            Artisan::call('nanotech:percentages');

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
