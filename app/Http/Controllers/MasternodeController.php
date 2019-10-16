<?php

namespace App\Http\Controllers;

use App\Enum\EnumMasternodeStatus;
use App\Models\Masternode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MasternodeController extends Controller
{
    public function list(Request $request)
    {
        try {
            $list = Masternode::where('user_id', auth()->user()->id)->paginate(10);
            $data = $list->makeHidden(['user_id']);
            $list->data = $data;
            return response($list, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function processing()
    {
        try {
            $masternode = Masternode::where([
                'user_id' => auth()->user()->id,
                'status' => EnumMasternodeStatus::PROCESSING,
            ])->first();
            return response($masternode, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
