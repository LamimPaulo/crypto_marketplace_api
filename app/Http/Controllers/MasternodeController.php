<?php

namespace App\Http\Controllers;

use App\Enum\EnumMasternodeOperation;
use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumUserWalletType;
use App\Models\Coin;
use App\Models\Masternode;
use App\Models\MasternodeInfo;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    'privkey' => $masternode->privkey,
                ];

                $return = self::post(EnumMasternodeOperation::ALLOC_NEW_ADDRESS, $data);
                $masternode->ip = preg_replace("/\r|\n/", "", $return['ipv6']);
                $masternode->payment_address = $return['ownerKeyAddr'];
                $masternode->fee_address = $return['feeSourceAddress'];

                $masternode->status = EnumMasternodeStatus::PROCESSING;
                $masternode->save();

                $wallet = UserWallet::where('address', $masternode->reward_address)->first();
                $wallet->address = $masternode->fee_address;
                $wallet->save();
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . ' => ' . $e->getLine() . ' => ' . $e->getFile());
        }
    }

    public static function updateTxids()
    {
        try {
            $masternodes = Masternode::where([
                'status' => EnumMasternodeStatus::PROCESSING,
            ])->inRandomOrder()->limit(env('MASTERNODE_CREATE', 0))->get();

            foreach ($masternodes as $masternode) {
                $data = [
                    'address' => $masternode->payment_address,
                ];

                $return = self::post(EnumMasternodeOperation::UPDATE_TXIDS, $data);

                $masternode->status = $return == 1 ? EnumMasternodeStatus::SUCCESS : $masternode->status;
                $masternode->save();
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . ' => ' . $e->getLine() . ' => ' . $e->getFile());
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
            throw new \Exception($e->getMessage() . ' => ' . $e->getLine() . ' => ' . $e->getFile());
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
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . ' => ' . $e->getLine() . ' => ' . $e->getFile());
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
                    'address' => $masternode->fee_address,
                    'type' => EnumUserWalletType::MASTERNODE,
                    'is_active' => true
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . ' => ' . $e->getLine() . ' => ' . $e->getFile());
        }
    }

    public static function confirmation($transaction)
    {
        try {
            DB::beginTransaction();
            $result = self::post(EnumOperationType::CONFIRMATION, ['txid' => $transaction->tx]);

            $data['confirmation'] = $result['confirmations'];

            if ($data['confirmation'] >= env("MASTERNODES_CONFIRMATIONS", 100)) {
                $data['status'] = EnumTransactionsStatus::SUCCESS;

                $data['status'] = EnumTransactionsStatus::BLOCKED;

                TransactionStatus::create([
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status
                ]);
            }

            $transaction->update($data);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex->getMessage();
        }
    }

    public static function info()
    {
        $count = Masternode::whereNotNull('privkey')->count();
        $total = Transaction::where('category', EnumTransactionCategory::MASTERNODE_REWARD)
            ->where('user_id', '<>', '8565eba3-465b-4d0a-bed6-d5ba99d52b68')
            ->sum('amount');

        $info = MasternodeInfo::first();

        if (!$info) {
            MasternodeInfo::create([
                'nodes' => $count,
                'rewards' => $total,
            ]);
        } else {
            $info->nodes = $count;
            $info->rewards = $total;
            $info->save();
        }
    }

}
