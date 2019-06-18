<?php

namespace App\Http\Controllers\Admin\Messages;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\CoinQuote;
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

//            $messages = Messages::with([
//                'user_id',
//                'subject'
//            ])->orderBy('created_at', 'DESC');

            $messages = Messages::with([])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

//            $data = $messages->getCollection();
//            $data->each(function ($item) {
//                $item->makeVisible(['id']);
//            });
//            $messages->setCollection($data);

//            return response([
//                'status' => 'success',
//                'count' => $messages->count(),
//                'messages' => $messages->get()
//            ], Response::HTTP_OK);

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
            $user_email = User::where('email', $request->user_email)->first();
            $user_id = $user_email->id;

            if ($user_id) {
                $message = new Messages([
                    'user_id' => $user_id,
                    'user_email' => $request->get('user_email'),
                    'type' => $request->get('type'),
                    'subject' => $request->get('subject'),
                    'content' => $request->get('content'),
                    'status' => $request->get('status')
                ]);

                $message->save();

                return response()->json('successfully added');
            } else {
                echo 'Id não existe';
            }
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

//    public function show($id)
    public function show($message_id)
    {
        try {

            $message = Messages::with([])->findOrFail($message_id);

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

//    public function show($id)
    public function edit()
    {
//        dd('fuck off');
        echo 'fuck off';
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
