<?php

namespace App\Http\Controllers\Admin\User;

use App\Enum\EnumNanotechOperationType;
use App\Enum\EnumStatusDocument;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumUserWalletType;
use App\Helpers\ActivityLogger;
use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\VerifyMail;
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
use App\UserRole;
use App\VerifyUser;
use Illuminate\Http\Request;
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

            return response([
                'message' => trans('messages.general.success'),
                'user' => $user,
                'permissions' => PermissionService::list(auth()->user()->id)
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
                ->orderBy('created_at', 'DESC')->paginate(10);

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
            'name' => 'required|min:3'
        ]);

        try {
            $users = User::with(['level'])
                ->where('email_verified_at', '<>', '')
                ->where('name', 'like', "%{$request->name}%")
                ->orWhere('email', 'like', "%{$request->name}%")
                ->orWhere('document', 'like', "%{$request->name}%")
                ->orderBy('name', 'ASC')->get();

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
                'masternodes' => $this->masternode($user->id),
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

    public function masternode($user_id)
    {
        try {
            $nanotech = Nanotech::with(['coin', 'type'])
                ->where('user_id', $user_id)
                ->where('type_id', 3)
                ->get();

            $array = [];

            foreach ($nanotech as $n) {
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
}
