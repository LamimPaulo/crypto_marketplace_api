<?php

namespace App\Http\Controllers\Admin\Messages;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use App\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index()
    {
        try {

            $messages = Messages::with(['user'])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);


            return response($messages, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'messages' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function notificationsList()
    {
        try {

            $messages = Messages::where([
                'type' => 0
            ])
                ->orWhereRaw('type = 1 AND user_id = "'.auth()->user()->id.'"')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);


            return response($messages, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'messages' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    // Novas Mensagens
    public function store(Request $request)
    {
        try{
            if ($request->user_email) {
                $user_email = User::where('email', $request->user_email)->first();
                $user_id = $user_email->id;
            } else {
                $user_id = 0;
            }

            $message = new Messages([
                'user_id' => $user_id,
                'type' => $request->get('type'),
                'subject' => $request->get('subject'),
                'content' => $request->get('content'),
                'status' => $request->get('status')
            ]);

            $message->save();

            return response()->json('successfully added - Enviada Para Usuário');

        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function edit($message_id)
    {
        try {

            $message = Messages::with(['user'])->findOrFail($message_id);

            if(!auth()->user()->is_admin){
                $message->status = 1;
                $message->save();
            }

            return response([
                'message' => trans('messages.general.success'),
                'content' => $message
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $message = Messages::where('id', $request->id)->first();

            $message->update($request->all());

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Mensagem Atualizada com Sucesso!'
            ],Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();

            $message = Messages::where('id', $request->id)->first();
            $message->delete($request->id);

            DB::commit();
            return response([
                'status' => 'success',
                'message'=> 'Mensagem Apagada!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function userList()
    {
        try {
            $users = User::where('email_verified_at', '<>', '')
                ->where('is_admin', 0)
                ->orderBy('name')->get();

            return response($users
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
