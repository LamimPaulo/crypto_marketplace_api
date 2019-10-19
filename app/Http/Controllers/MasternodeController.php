<?php

namespace App\Http\Controllers;

use App\Enum\EnumMasternodeOperation;
use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumUserWalletType;
use App\Models\Coin;
use App\Models\Masternode;
use App\Models\MasternodeInfo;
use App\Models\User\UserWallet;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

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

    public function foundersInfo()
    {
        try {
            $masternode = MasternodeInfo::first();
            $mynodes = Masternode::where([
                'user_id' => auth()->user()->id,
                'status' => EnumMasternodeStatus::SUCCESS
            ])->count();

            $percent = round(100 * $mynodes / 3, 2);

            return response([
                'nodes' => $masternode->nodes,
                'rewards' => $masternode->rewards,
                'mynodes' => $mynodes,
                'percent' => $percent,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    //Comands
    public static function proccessPending()
    {
        try {
            $masternodes = Masternode::where([
                'status' => EnumMasternodeStatus::PENDING,
            ])->get();

            foreach ($masternodes as $masternode) {
                $data = [
                    'address' => $masternode->reward_address,
                ];

                $return = self::post(EnumMasternodeOperation::ALLOC_NEW_ADDRESS, $data);

                $masternode->ip = $return['ipv6'];
                $masternode->payment_address = $return['ownerKeyAddr'];
                $masternode->fee_address = $return['feeSourceAddress'];

                $masternode->status = EnumMasternodeStatus::PROCESSING;
                $masternode->save();
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function updateTxids()
    {
        try {
            $masternodes = Masternode::where([
                'status' => EnumMasternodeStatus::PROCESSING,
            ])->get();

            foreach ($masternodes as $masternode) {
                $data = [
                    'address' => $masternode->payment_address,
                ];

                $return = self::post(EnumMasternodeOperation::UPDATE_TXIDS, $data);

                if (isset($return['txid']) AND !empty($return['txid'])) {
                    $masternode->status = EnumMasternodeStatus::SUCCESS;
                    $masternode->save();
                }
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function cancelMasternodes()
    {
        try {
            $masternodes = Masternode::where([
                'status' => EnumMasternodeStatus::CANCELED,
            ])->get();

            foreach ($masternodes as $masternode) {
                $data = [
                    'ownerKeyAddr' => $masternode->payment_address,
                ];

                if (self::post(EnumMasternodeOperation::SUSPEND_NODE, $data)) {
                    $masternode->status = EnumMasternodeStatus::SUSPENDED;
                    $masternode->save();
                }
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function info()
    {
        try {
            $return = self::post(EnumMasternodeOperation::SUSPEND_NODE, null);
            $info = MasternodeInfo::firstOrNew();
            $info->rewards = $return['rewards'];
            $info->nodes = $return['nodes'];
            $info->save();

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function post($type, $data = "")
    {
        try {

            $result = (new Client())->post(config("services.masternode.api"), [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'data' => encrypt([
                        'data' => $data,
                        'type' => $type
                    ])
                ]
            ]);

            $response = $result->getBody()->getContents();
            $response = decrypt($response);

            if (!isset($response['error'])) {
                return $response;
            } else {
                throw new \Exception($response['error']);
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public static function import()
    {
        try {

            Masternode::all()->each(function ($masternode) {
                UserWallet::firstOrCreate([
                    'user_id' => $masternode->user_id,
                    'coin_id' => Coin::getByAbbr("LQX")->id,
                    'balance' => 0,
                    'address' => $masternode->reward_address,
                    'type' => EnumUserWalletType::MASTERNODE,
                    'is_active' => true
                ]);
            });
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

}
