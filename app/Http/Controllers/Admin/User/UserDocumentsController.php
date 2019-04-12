<?php

namespace App\Http\Controllers\Admin\User;

use App\Enum\EnumStatusDocument;
use App\Helpers\ActivityLogger;
use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\DocumentReject;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class UserDocumentsController extends Controller
{
    public function index()
    {
        try {
            $users = User::with('documents')
                        ->whereHas('documents', function ($documents) {
                            return $documents->with('type')->where('status', EnumStatusDocument::PENDING);
                        })->orderBy('created_at')->paginate(10);
            return response($users, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function search(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3'
        ]);

        try {
            $users = User::with('documents')
                        ->whereHas('documents', function ($documents) {
                            return $documents->with('type')->where('status', EnumStatusDocument::PENDING);
                        })->where('name', 'like', "%{$request->name}%")
                ->orderBy('name', 'ASC')->get();

            return response(['data' => $users]
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function reject(Request $request)
    {
        $request->validate([
            'user_email' => 'required|exists:users,email',
            'reason' => 'required|min:3'
        ], [
            'user_email.required' => trans('validation.documents.reject.user_email_required'),
            'user_email.exists' => trans('validation.documents.reject.user_email_exists'),
            'reason.required' => trans('validation.documents.reject.reason_required'),
        ]);

        try {
            DB::beginTransaction();

            $user = User::with('documents')->where('email', $request->user_email)->first();

            foreach($user->documents as $doc){
                $doc->status = EnumStatusDocument::INVALID;
                $doc->save();
            }

            if($user->country_id!=31){
                $user->document = null;
                $user->save();
            }

            Localization::setLocale($user);
            Mail::to($user->email)->send(new DocumentReject($user, $request->reason));

            ActivityLogger::log(trans('messages.documents.reject', ['reason' => $request->reason]), $user->id);

            DB::commit();
            return response([
                'message' => 'Os documentos foram rejeitados com sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function accept(Request $request)
    {
        $request->validate([
            'user_email' => 'required|exists:users,email',
        ], [
            'user_email.required' => trans('validation.documents.reject.user_email_required'),
            'user_email.exists' => trans('validation.documents.reject.user_email_exists'),
        ]);

        try {
            DB::beginTransaction();

            $user = User::with('documents')->where('email', $request->user_email)->first();

            foreach($user->documents as $doc){
                $doc->status = EnumStatusDocument::VALID;
                $doc->save();
            }

            $user->user_level_id = 3;
            $user->document_verified = 1;
            $user->save();

            ActivityLogger::log(trans('messages.documents.accept'), $user->id);
            ActivityLogger::log(trans('messages.general.level_up'), $user->id);

            DB::commit();
            return response([
                'message' => 'Documentação aprovada com sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
