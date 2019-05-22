<?php

namespace App\Http\Controllers\Admin\User;

use App\Enum\EnumNanotechOperationType;
use App\Enum\EnumStatusDocument;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Models\Funds\FundBalances;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Transaction;
use App\Models\User\Document;
use App\Models\User\UserAccount;
use App\Models\User\UserWallet;
use App\User;
use Illuminate\Http\Request;
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
                'user' => $user
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

    public function remove2fa($user_email) {
        try {
            $user = User::where('email', $user_email)->first();

            $user->google2fa_secret = null;
            $user->is_google2fa_active = false;
            $user->save();

            ActivityLogger::log(trans('messages.2fa.deactivated'), $user->email);

            return response([
                'message' => trans('messages.2fa.deactivated'),
                'user' => $user
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
