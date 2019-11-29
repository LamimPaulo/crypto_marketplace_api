<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumMasternodeStatus;
use App\Models\Masternode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MasternodesController extends Controller
{
    public function list(Request $request)
    {
        try {
            $masternodes = Masternode::with('user');

            if (!is_null($request->name) && !empty($request->name)) {
                $masternodes->whereHas('user', function ($user) use ($request) {
                    $user->where('name', 'like', "%{$request->name}%");
                    $user->orWhere('email', 'like', "%{$request->name}%");
                    $user->orWhere('document', 'like', "%{$request->name}%");
                    $user->orWhere('api_key', 'like', "%{$request->name}%");
                });
            }

            if (!is_null($request->status) && !empty($request->status)) {
                $masternodes->where('status', $request->status);
            }

            if (!is_null($request->wallet) && !empty($request->wallet)) {
                $masternodes->where('fee_address', 'like', "%{$request->wallet}%")
                    ->orWhere('payment_address', 'like', "%{$request->wallet}%")
                    ->orWhere('reward_address', 'like', "%{$request->wallet}%");
            }

            return response($masternodes->orderByDesc('updated_at')->paginate(10), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function index()
    {
        try {
            $resume['all'] = Masternode::count();
            foreach (EnumMasternodeStatus::TYPE as $k => $status) {
                $resume[$status] = Masternode::where('status', $k)->count();
            }
            return response([
                'resume' => $resume,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
