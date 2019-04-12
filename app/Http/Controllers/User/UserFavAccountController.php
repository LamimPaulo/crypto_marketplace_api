<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserFavAccountRequest;
use App\Models\User\UserFavAccount;
use App\User;
use Symfony\Component\HttpFoundation\Response;

class UserFavAccountController extends Controller
{
    public function index()
    {
        $users = UserFavAccount::with('fav_user')->where('user_id', auth()->user()->id)->get();
        $accounts = [];
        foreach ($users as $user) {
            $accounts[] = [
                'name' => $user->fav_user->name,
                'email' => $user->fav_user->email
            ];
        }
        if (count($accounts) > 0) {
            return response([
                'message' => trans('messages.general.success'),
                'accounts' => $accounts,
                'count' => count($accounts),
            ], Response::HTTP_OK);
        }

        return response([
            'message' => trans('messages.account.not_found'),
            'count' => count($accounts),
            'accounts' => null
        ], Response::HTTP_NOT_FOUND);
    }

    public function store(UserFavAccountRequest $request)
    {
        try {
            $beneficiary = User::where('email', $request->email)->firstOrFail();
            if (!$beneficiary) {
                throw new \Exception(trans('messages.account.beneficiary_already_registered'));
            }

            $exists = UserFavAccount::where('fav_user_id', $beneficiary->id)->exists();
            if ($exists) {
                throw new \Exception(trans('messages.account.beneficiary_already_registered'));
            }

            if ($request->email == auth()->user()->email) {
                throw new \Exception(trans('messages.account.you_could_not_be_recipient'));
            }

            UserFavAccount::create([
                'user_id' => auth()->user()->id,
                'fav_user_id' => $beneficiary->id
            ]);

            $account = User::find($beneficiary->id);

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

    public function show($email)
    {
        try {
            $beneficiary = User::where('email', $email)->firstOrFail();
            if (!$beneficiary) {
                throw new \Exception(trans('messages.account.beneficiary_already_registered'));
            }

            $exists = UserFavAccount::where(['user_id' => auth()->user()->id, 'fav_user_id' => $beneficiary->id])->exists();
            if (!$exists) {
                throw new \Exception(trans('messages.account.beneficiary_not_found'));
            }

            $account = User::findOrFail($beneficiary->id);

            return response([
                'message' => trans('messages.general.success'),
                'account' => $account
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(),], Response::HTTP_BAD_REQUEST);
        }
    }

    public function search(UserFavAccountRequest $request)
    {
        try {
            $beneficiary = User::where('email', $request->email)->firstOrFail();
            $exists = UserFavAccount::where(['user_id' => auth()->user()->id, 'fav_user_id' => $beneficiary->id])->exists();
            if ($exists) {
                throw new \Exception(trans('messages.account.beneficiary_already_registered'));
            }

            if ($request->email == auth()->user()->email) {
                throw new \Exception(trans('messages.account.you_could_not_be_recipient'));
            }

            $account = User::findOrFail($beneficiary->id);

            return response([
                'message' => trans('messages.general.success'),
                'account' => $account
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(),], Response::HTTP_BAD_REQUEST);
        }
    }
}
