<?php

namespace App\Http\Controllers;

use App\Enum\EnumMasternodeOperation;
use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Models\Coin;
use App\Models\Masternode;
use App\Models\MasternodeInfo;
use App\Models\MasternodePlan;
use App\Models\MasternodeUserPlan;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
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

    public function rewards($address)
    {
        try {
            $list = Transaction::where([
                'user_id' => auth()->user()->id,
                'toAddress' => $address,
                'category' => EnumTransactionCategory::MASTERNODE_REWARD
            ])
                ->orderByDesc('created_at')
                ->paginate(8);

            return response($list, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function verifyPendingPayments()
    {
        try {
            $pendingPlans = MasternodeUserPlan::where([
                'user_id' => auth()->user()->id,
                'status' => EnumMasternodeStatus::PENDING_PAYMENT
            ])->count();

            $pendingMasternodes = Masternode::where([
                'user_id' => auth()->user()->id,
                'status' => EnumMasternodeStatus::PENDING_PAYMENT
            ])->count();

            return response([
                'pending_plans' => $pendingPlans,
                'pending_masternodes' => $pendingMasternodes,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function listPendingPayment()
    {
        try {
            $pendingPlan = MasternodeUserPlan::with(['plan', 'masternode'])->where([
                'user_id' => auth()->user()->id,
                'status' => EnumMasternodeStatus::PENDING_PAYMENT
            ])
                ->orderBy('end_date')
                ->orderBy('masternode_id');

            $plans = MasternodePlan::all();

            $pendingMasternodes = [];

            if (!$pendingPlan->count()) {
                $pendingMasternodes = Masternode::where([
                    'user_id' => auth()->user()->id,
                    'status' => EnumMasternodeStatus::PENDING_PAYMENT
                ])->paginate(10);
            }

            return response([
                'pending_masternodes' => $pendingMasternodes,
                'pending_plans' => $pendingPlan->paginate(10),
                'plans' => $plans,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function listAllPlans()
    {
        try {
            $masternodePlans = MasternodeUserPlan::with(['plan', 'masternode'])->where([
                'user_id' => auth()->user()->id
            ])
                ->orderByDesc('end_date')
                ->orderBy('masternode_id');

            return response([
                'masternode_plans' => $masternodePlans->paginate(10),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function acceptPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $pendingPlan = MasternodeUserPlan::where([
                'user_id' => auth()->user()->id,
                'status' => EnumMasternodeStatus::PENDING_PAYMENT,
                'id' => $request->id
            ])
                ->whereHas('masternode', function ($masternode) {
                    return $masternode->where([
                        'user_id' => auth()->user()->id,
                        'status' => EnumMasternodeStatus::PENDING_PAYMENT,
                    ]);
                })
                ->firstOrFail();

//            Verificar se possui saldo na carteira
            $from = UserWallet::where([
                'user_id' => auth()->user()->id,
                'coin_id' => Coin::getByAbbr("LQX")->id,
                'type' => EnumUserWalletType::WALLET
            ])->first();

            if (!$from) {
                throw new \Exception(trans('messages.wallet.invalid'));
            }

            if (abs($pendingPlan->plan->value) <= abs($from->balance)) { //Saldo Carteira
                $this->contabilizeRewards($pendingPlan, $from);
            } else { // Saldo recompensas
                $rewards = Transaction::where([
                    'status' => EnumTransactionsStatus::BLOCKED,
                    'category' => EnumTransactionCategory::MASTERNODE_REWARD,
                    'toAddress' => $pendingPlan->masternode->fee_address,
                    'user_id' => auth()->user()->id,
                ])->whereBetween('created_at', [
                    $pendingPlan->start_date->format("Y-m-d 00:00:00"),
                    $pendingPlan->end_date->format("Y-m-d 23:59:59")
                ])->sum('amount');

                if (!(abs($pendingPlan->plan->value) <= abs($rewards))) {
                    throw new \Exception(trans('messages.transaction.value_exceeds_balance'));
                }

                $this->contabilizeRewards($pendingPlan, $from);
            }

            $transaction_out = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $from->coin_id,
                'wallet_id' => $from->id,
                'amount' => $pendingPlan->plan->value,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::MASTERNODE,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => '**Mensalidade Masternode',
                'error' => '',
                'price' => 0,
                'market' => 0
            ]);

            BalanceService::decrements($transaction_out);

            $pendingPlan->status = EnumMasternodeStatus::SUCCESS;
            $pendingPlan->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Pagamento Efetuado com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function refusePayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $pendingPlan = MasternodeUserPlan::where([
                'user_id' => auth()->user()->id,
                'status' => EnumMasternodeStatus::PENDING_PAYMENT,
                'id' => $request->id
            ])
                ->whereHas('masternode', function ($masternode) {
                    return $masternode->where([
                        'user_id' => auth()->user()->id,
                        'status' => EnumMasternodeStatus::PENDING_PAYMENT,
                    ]);
                })
                ->firstOrFail();

            $rewards = Transaction::where([
                'status' => EnumTransactionsStatus::BLOCKED,
                'category' => EnumTransactionCategory::MASTERNODE_REWARD,
                'toAddress' => $pendingPlan->masternode->fee_address,
                'user_id' => auth()->user()->id,
            ])->whereBetween('created_at', [
                $pendingPlan->start_date->format("Y-m-d 00:00:00"),
                $pendingPlan->end_date->format("Y-m-d 23:59:59")
            ])->get();

            foreach ($rewards as $reward) {
                $reward->status = EnumTransactionsStatus::REFUSED;
                $reward->save();

                TransactionStatus::create([
                    'status' => $reward->status,
                    'transaction_id' => $reward->id,
                ]);
            }

            $pendingPlan->status = EnumMasternodeStatus::REFUSED;
            $pendingPlan->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Recompensas recusadas com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function contabilizeRewards($plan, $wallet)
    {
        try {
            $rewards = Transaction::where([
                'status' => EnumTransactionsStatus::BLOCKED,
                'category' => EnumTransactionCategory::MASTERNODE_REWARD,
                'toAddress' => $plan->masternode->fee_address,
                'user_id' => auth()->user()->id,
            ])->whereBetween('created_at', [
                $plan->start_date->format("Y-m-d 00:00:00"),
                $plan->end_date->format("Y-m-d 23:59:59")
            ])->get();

            foreach ($rewards as $reward) {
                $reward->status = EnumTransactionsStatus::SUCCESS;
                $reward->save();

                TransactionStatus::create([
                    'status' => $reward->status,
                    'transaction_id' => $reward->id,
                ]);

                $reward->wallet_id = $wallet->id;
                BalanceService::increments($reward);
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getTraceAsString());
        }
    }

    public function undo(Request $request)
    {
        try {
            DB::beginTransaction();

            $masternode = Masternode::where([
                'user_id' => auth()->user()->id,
                'id' => $request->id
            ])
                ->whereIn('status', [EnumMasternodeStatus::SUCCESS, EnumMasternodeStatus::PENDING_PAYMENT])
                ->first();

            $masternode->hist()->create([
                'user_id' => $masternode->user_id,
                'status' => $masternode->status,
                'info' => "Início do desfazimento do masternode",
            ]);

            $from = UserWallet::where([
                'user_id' => auth()->user()->id,
                'coin_id' => Coin::getByAbbr("LQX")->id,
                'type' => EnumUserWalletType::WALLET
            ])->first();

            //Verificar se existem planos futuros ja pagos
            $plans = MasternodeUserPlan::with('plan')
                ->where([
                    'user_id' => auth()->user()->id,
                    'masternode_id' => $masternode->id,
                    'status' => EnumMasternodeStatus::SUCCESS
                ])
                ->where('end_date', '>', Carbon::now()->format('Y-m-d 00:00:00'))
                ->get();

            //estornar pagamentos dos planos futuros
            foreach ($plans as $plan) {
                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'coin_id' => $from->coin_id,
                    'wallet_id' => $from->id,
                    'amount' => $plan->plan->value,
                    'status' => EnumTransactionsStatus::SUCCESS,
                    'type' => EnumTransactionType::IN,
                    'category' => EnumTransactionCategory::MASTERNODE,
                    'fee' => 0,
                    'tax' => 0,
                    'tx' => Uuid::uuid4()->toString(),
                    'info' => '**Estorno Mensalidade Masternode',
                    'error' => '',
                    'price' => 0,
                    'market' => 0
                ]);

                BalanceService::increments($transaction);

                $plan->status = EnumMasternodeStatus::CANCELED;
                $plan->save();
            }

            $masternode->user_id = env("LIQUIDEX_USER");
            $masternode->status = EnumMasternodeStatus::CANCELED;
            $masternode->save();

            $masternode->hist()->create([
                'user_id' => env("LIQUIDEX_USER"),
                'status' => $masternode->status,
                'info' => "Finalizado desfazimento do masternode",
            ]);

            $masternode->wallet->user_id = env("LIQUIDEX_USER");
            $masternode->wallet->save();

            //criar um lançamento de entrada (estorno dos 1000LQX) com identificação do fee_addres
            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $from->coin_id,
                'wallet_id' => $from->id,
                'toAddress' => $masternode->fee_address,
                'amount' => '1000',
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::MASTERNODE_UNDO,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => '**Desfazimento Masternode',
                'error' => '',
                'price' => 0,
                'market' => 0
            ]);

            BalanceService::increments($transaction);

            DB::commit();
            return response([
                'status' => 'error',
                'message' => 'Masternode Cancelado com sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
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

            $percent = round((100 * $mynodes) / 3, 2);

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

                if (!$transaction->user_id == env("LIQUIDEX_USER")) {
                    $data['status'] = EnumTransactionsStatus::BLOCKED;
                }

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
            ->where('user_id', '<>', env("LIQUIDEX_USER"))
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
