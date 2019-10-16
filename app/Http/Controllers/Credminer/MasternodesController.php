<?php

namespace App\Http\Controllers\Credminer;

use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumOperationType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OffScreenController;
use App\Models\Coin;
use App\Models\Masternode;
use App\Models\User\UserWallet;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class MasternodesController extends Controller
{
    public function index(Request $request)
    {
        Validator::make($request->all(), [
            'keycode' => 'required|exists:users,api_key'
        ], [
            'keycode.required' => "O keycode deve ser informado.",
            'keycode.exists' => "O keycode informado é inválido.",
        ])->validate();

        try {
            $user = User::where('api_key', '=', $request->keycode)->first();

            return response([
                'status' => 'success',
                'masternodes' => Masternode::where('user_id', $user->id)->get()
                    ->makeHidden(Masternode::hiddenAttr())
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function create(Request $request)
    {

        Validator::make($request->all(), [
            'keycode' => 'required|exists:users,api_key',
            'amount' => 'required|numeric|min:1',
        ], [
            'keycode.required' => "O keycode deve ser informado.",
            'keycode.exists' => "O keycode informado é inválido.",
            'amount.required' => "A quantidade de masternodes deve ser informada.",
            'amount.numeric' => "A quantidade de masternodes deve ser informada corretamente.",
            'amount.min' => "A quantidade de masternodes informada deve ser superior a 1.",
        ])->validate();

        try {
            if ($request->amount < 1) {
                throw new \Exception("A quantidade miníma de compra deve ser superior a 0.");
            }

            $user = User::where('api_key', '=', $request->keycode)->first();

            DB::beginTransaction();

            for ($i = 0; $i < $request->amount; $i++) {
                $address = env('APP_ENV') == 'local' ? Uuid::uuid4()->toString() :
                    OffScreenController::post(EnumOperationType::CREATE_ADDRESS, NULL, "LQX");

                $wallet = UserWallet::create([
                    'user_id' => $user->id,
                    'coin_id' => Coin::getByAbbr("LQX")->id,
                    'balance' => 0,
                    'address' => $address,
                    'type' => EnumUserWalletType::MASTERNODE,
                    'is_active' => true
                ]);

                Masternode::create([
                    'coin_id' => $wallet->coin_id,
                    'user_id' => $wallet->user_id,
                    'reward_address' => $wallet->address,
                    'status' => EnumMasternodeStatus::PENDING,
                ]);
            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => trans('messages.products.hiring_success'),
                'masternodes' => Masternode::where('user_id', $user->id)->get()
                    ->makeHidden(Masternode::hiddenAttr())
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
