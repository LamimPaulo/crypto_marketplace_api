<?php

namespace App\Http\Controllers\Admin\User;

use App\Enum\EnumNanotechOperationType;
use App\Enum\EnumStatusDocument;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumUserWalletType;
use App\Helpers\Localization;
use App\Helpers\ActivityLogger;
use App\Models\Masternode;
use App\Models\System\ActivityLogger as UserLogger;
use App\Http\Controllers\Controller;
use App\Mail\VerifyMail;
use App\Mail\UserReactivatedMail;
use App\Models\Funds\FundBalances;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Transaction;
use App\Models\User\Document;
use App\Models\User\UserAccount;
use App\Models\User\UserEmailChange;
use App\Models\User\UserWallet;
use App\Services\PermissionService;
use App\User;
use App\VerifyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    //logged user data
    public function index()
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            $user['permissions'] = PermissionService::list(auth()->user()->id);

            return response([
                'message' => trans('messages.general.success'),
                'user' => $user,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function list()
    {
        try {
            $users = User::with(['level'])
                ->where('email_verified_at', '<>', '')
                ->where('is_admin', 0)
                ->where('is_canceled', 0)
                ->orderBy('created_at', 'DESC')->paginate(10);

            return response($users
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function balance()
    {
        try {
            $users = User::with(['level', 'wallets' => function ($coin) {
                return $coin->whereHas('coin', function ($abbr) {
                    return $abbr->where('abbr', 'LQX');
                });
            }])
                ->whereHas('wallets', function ($wallets) {
                    return $wallets->whereHas('coin', function ($coin) {
                        return $coin->where('abbr', 'LQX');
                    });
                })
                ->where('email_verified_at', '<>', '')
                ->where('is_admin', 0)
                ->where('is_canceled', 0)
                ->orderBy('created_at', 'DESC')->paginate(10);

            return response($users
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function searchBalance(Request $request)
    {
        try {
            $users = User::with(['level', 'wallets' => function ($coin) use ($request) {
                return $coin->whereHas('coin', function ($abbr) use ($request) {
                    return $abbr->where('abbr', $request->abbr);
                });
            }])
                ->whereHas('wallets', function ($wallets) use ($request) {
                    return $wallets->whereHas('coin', function ($coin) use ($request) {
                        return $coin->where('abbr', $request->abbr);
                    });
                })
                ->where('email_verified_at', '<>', '')
                // ->where('is_admin', 0)
                ->where('is_canceled', 0)
                ->orderBy('created_at', 'DESC')->paginate(10);

            return response($users
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function listDeactivated()
    {
        try {
            $users = User::where('is_canceled', 1)
                ->orderBy('updated_at', 'DESC')->paginate(10);

            return response($users
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function incomplete()
    {
        try {
            $users = User::whereNull('email_verified_at')
                ->orderBy('created_at', 'DESC')->paginate(10);
            return response($users, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function search(Request $request)
    {
        $request->validate([
            'name' => 'required_without:wallet',
            'wallet' => 'required_without:name',
        ]);

        try {
            $users = User::with(['level'])
                ->orderBy('name', 'ASC')
                ->where('is_canceled', 0)
                ->where('email_verified_at', '<>', '');

            if (!is_null($request->name) && !empty($request->name)) {
                $users->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->name}%");
                    $q->orWhere('email', 'like', "%{$request->name}%");
                    $q->orWhere('document', 'like', "%{$request->name}%");
                    $q->orWhere('api_key', 'like', "%{$request->name}%");
                });
            }

            if (!is_null($request->wallet) && !empty($request->wallet)) {
                $users->whereHas('wallets', function ($wallet) use ($request) {
                    $wallet->where('address', 'like', "%{$request->wallet}%");
                });
            }


            return response(['data' => $users->get()]
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function searchDeactivated(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3'
        ]);


        try {
            $users = User::with(['level'])
                ->orderBy('name', 'ASC')
                ->where('is_canceled', 1)
                ->where('email_verified_at', '<>', '')
                ->Where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->name}%");
                    $q->orWhere('email', 'like', "%{$request->name}%");
                    $q->orWhere('document', 'like', "%{$request->name}%");
                })
                ->get();

            return response(['data' => $users]
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function hist(Request $request)
    {
        $request->validate(['email' => 'required|exists:users,email']);

        try {
            $user = User::with(['level'])->where('email', $request->email)->firstOrFail();
            $wallets = UserWallet::with(['coin'])->where(['user_id' => $user->id, 'type' => EnumUserWalletType::WALLET])->get();
            $accounts = UserAccount::with(['bank'])->where(['user_id' => $user->id])->get();

            $funds = FundBalances::with([
                'fund' => function ($fund) {
                    return $fund->with('coin');
                }
            ])->where('user_id', $user->id)->orderBy('fund_id')->get();

            return response([
                'user' => $user,
                'wallets' => $wallets,
                'accounts' => $accounts,
                'nanotech' => $this->nanotech($user->id),
                'funds' => $funds,
                'documents' => $this->documents($user->id)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function nanotech($user_id)
    {
        try {
            $nanotech = Nanotech::with(['coin', 'type'])
                ->where('user_id', $user_id)
                ->where('type_id', '<>', 3)
                ->get();

            $array = [];

            foreach ($nanotech as $n) {
                $from_balance = NanotechOperation::where('type', EnumNanotechOperationType::IN)
                    ->where('user_id', $user_id)
                    ->where('investment_id', $n->id)
                    ->where('brokerage_fee', '>', 0)
                    ->sum('amount');

                $from_brokerage = NanotechOperation::where('type', EnumNanotechOperationType::IN)
                    ->where('user_id', $user_id)
                    ->where('investment_id', $n->id)
                    ->where('brokerage_fee', '>', 0)
                    ->sum('brokerage_fee');

                $array[] = [
                    'coin' => $n->coin->abbr,
                    'name' => $n->type->type,
                    'balance' => $n->amount,
                    'profit' => NanotechOperation::whereIn('type',
                        [
                            EnumNanotechOperationType::PROFIT,
                            EnumNanotechOperationType::PROFIT_WITHDRAWAL,
                            EnumNanotechOperationType::PROFIT_IN
                        ])
                        ->where('user_id', $user_id)
                        ->where('investment_id', $n->id)
                        ->sum('amount'),

                    'profit_generated' => NanotechOperation::whereIn('type', [EnumNanotechOperationType::PROFIT])
                        ->where('user_id', $user_id)
                        ->where('investment_id', $n->id)
                        ->sum('amount'),
                    'profit_withdrawal' => NanotechOperation::whereIn('type', [EnumNanotechOperationType::PROFIT_WITHDRAWAL])
                        ->where('user_id', $user_id)
                        ->where('investment_id', $n->id)
                        ->sum('amount'),
                    'investment' => NanotechOperation::whereIn('type', [EnumNanotechOperationType::IN, EnumNanotechOperationType::PROFIT_IN])
                        ->where('user_id', $user_id)
                        ->where('investment_id', $n->id)
                        ->sum('amount'),
                    'investment_from_balance' => $from_balance + $from_brokerage,
                    'investment_withdrawal' => NanotechOperation::whereIn('type', [EnumNanotechOperationType::WITHDRAWAL])
                        ->where('user_id', $user_id)
                        ->where('investment_id', $n->id)
                        ->sum('amount')
                ];
            }

            return $array;

        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function documents($user_id)
    {
        $document = Document::where('document_type_id', 1)->where('user_id', $user_id)->first();
        $document_status = $document->status ?? 0;

        $selfie = Document::where('document_type_id', 2)->where('user_id', $user_id)->first();
        $selfie_status = $selfie->status ?? 0;

        return [
            'document' => [
                'status' => EnumStatusDocument::STATUS[$document_status],
                'message' => EnumStatusDocument::MESSAGE[app()->getLocale()][$document_status]
            ],
            'selfie' => [
                'status' => EnumStatusDocument::STATUS[$selfie_status],
                'message' => EnumStatusDocument::MESSAGE[app()->getLocale()][$selfie_status]
            ],
        ];
    }

    public function transactions(Request $request)
    {
        $request->validate(['email' => 'required|exists:users,email']);

        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $transactions = Transaction::with('coin', 'user_account')
                ->where('user_id', $user->id)
                ->whereNotIn('category', [EnumTransactionCategory::WITHDRAWAL, EnumTransactionCategory::DEPOSIT])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return response($transactions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function transactionsNanotech(Request $request)
    {
        $request->validate(['email' => 'required|exists:users,email']);

        try {
            $user = User::where('email', $request->email)->firstOrFail();

            $transactions = NanotechOperation::with([
                'investment' => function ($investment) {
                    return $investment->with(['type', 'coin']);
                }
            ])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'DESC')
                ->get();

            return response($transactions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function drafts(Request $request)
    {
        $request->validate(['email' => 'required|exists:users,email']);

        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin',
                'user_account' => function ($account) {
                    return $account->with('bank');
                }])
                ->where('user_id', $user->id)
                ->where('category', EnumTransactionCategory::WITHDRAWAL)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return response($transactions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deposits(Request $request)
    {
        $request->validate(['email' => 'required|exists:users,email']);

        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin',
                'system_account' => function ($account) {
                    return $account->with('bank');
                }])
                ->where('user_id', $user->id)
                ->where('category', EnumTransactionCategory::DEPOSIT)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return response($transactions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function remove2fa($email)
    {

        try {
            $user = User::where('email', $email)->firstOrFail();

            if ($user->is_google2fa_active) {

                $user->google2fa_secret = null;
                $user->is_google2fa_active = false;
                $user->save();

                ActivityLogger::log(trans('messages.2fa.deactivated'), $user->id);

                return response([
                    'message' => trans('messages.2fa.deactivated'),
                    'user' => $user
                ], Response::HTTP_OK);
            }

            throw new \Exception(trans('messages.2fa.not_activated'));

        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function reactivateUser($email)
    {

        try {
            $user = User::where('email', $email)->firstOrFail();

            if ($user->is_canceled) {

                $user->is_canceled = false;
                $user->save();

                ActivityLogger::log(trans('messages.account.reactivated'), $user->id);
                Mail::to($user->email)->send(new UserReactivatedMail($user));

                return response([
                    'message' => trans('messages.account.reactivated'),
                ], Response::HTTP_OK);


            }

            throw new \Exception(trans(''));

        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateEmail(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::where('email', $request->old_email)->firstOrFail();

            if ($request->email === $request->old_email) {
                throw new \Exception("O novo email é igual ao antigo, nenhuma modificação foi realizada.");
            }

            $newEmail = User::where('email', $request->email)->first();

            if ($newEmail) {
                throw new \Exception("O novo email já está registrado com outro usuário da plataforma e não pode ser atribuído.");
            }

            UserEmailChange::create([
                'old_email' => $request->old_email,
                'new_email' => $request->email,
                'user_id' => $user->id,
                'creator_id' => auth()->user()->id
            ]);

            $user->email = $request->email;
            $user->email_verified_at = null;
            $user->save();

            DB::statement("DELETE FROM verify_users WHERE user_id = '$user->id'");

            VerifyUser::create([
                'user_id' => $user->id,
                'token' => Uuid::uuid4()->toString()
            ]);

            Localization::setLocale($user);
            Mail::to($user->email)->send(new VerifyMail($user));

            DB::commit();
            return response([
                'message' => trans('messages.general.success') . " Foi enviado um email de confirmação para o usuário.",
                'user' => $user
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function userActivity($email)
    {
        try {
            $user = User::where('email', $email)->first();

            $users = UserLogger::with(['causer', 'user'])
                ->where('subject_id', $user->id)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);


            return response($users
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function makeAdmin($email)
    {

        try {
            $user = User::where('email', $email)->firstOrFail();

            $user->is_admin = true;
            $user->save();

            Artisan::call('db:seed --class=AclSeeder --force');

            ActivityLogger::log("Tornou-se Admin.", $user->id);

            return response([
                'message' => trans('messages.general.success'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function revogueAdmin($email)
    {

        try {
            $user = User::where('email', $email)->firstOrFail();

            $user->is_admin = false;
            $user->save();

            ActivityLogger::log("Seu acesso Admin foi revogado.", $user->id);

            return response([
                'message' => trans('messages.general.success'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function masternodes(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            $list = Masternode::where('user_id', $user->id)->paginate(10);
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
}
