<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAccountRequest;
use App\Models\User\UserAccount;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAccountController extends Controller
{
    public function index()
    {
        $accounts = UserAccount::with(['bank'])->where('user_id', auth()->user()->id);

        if ($accounts->count() > 0) {
            return response([
                'message' => trans('messages.general.success'),
                'count' => $accounts->count(),
                'accounts' => $accounts->get()
            ], Response::HTTP_OK);
        }

        return response([
            'message' => trans('messages.account.not_found'),
            'count' => $accounts->count(),
            'accounts' => $accounts->get()
        ], Response::HTTP_NOT_FOUND);
    }

    public function show($account)
    {
        try {
            $account = UserAccount::with(['bank'])->where('user_id', auth()->user()->id)->findOrFail($account);
            return response([
                'message' => trans('messages.general.success'),
                'account' => $account
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(UserAccountRequest $request)
    {
        try {
            $request['user_id'] = auth()->user()->id;

            if (auth()->user()->country_id === 31) {
                $request->validate([
                    'bank_id'   => 'required|exists:banks,id',
                ]);

                $account = UserAccount::create($request->all());
            }else{
                $request->validate([
                    'bank_name'   => 'required'
                ]);

                $account = UserAccount::create($request->all());
            }

            return response([
                'message' => trans('messages.account.created'),
                'account' => $account
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(UserAccountRequest $request)
    {
        try {
            $account = UserAccount::where(['user_id' => auth()->user()->id, 'id' => $request->id])->first();

            $account->update($request->all());

            return response([
                'message' => trans('messages.account.updated'),
                'account' => $account
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(Request $request)
    {
//        return $request->all();
        try {
            $account = UserAccount::where('user_id', auth()->user()->id)->findOrFail($request->account);
            $account->delete();

            return response([
                'message' => trans('messages.account.deleted'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
