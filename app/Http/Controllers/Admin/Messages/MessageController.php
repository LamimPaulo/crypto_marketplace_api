<?php

namespace App\Http\Controllers\Admin\Messages;

use App\Http\Controllers\Controller;
use App\Models\MessageStatus;
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

//    Lista de Notificações no front
    public function notificationsList()
    {
        try {
            $messages = Messages::with([
                'user',
                'statuses' => function ($statuses) {
                    return $statuses->where('user_id', auth()->user()->id);
                }
            ])->where('user_id', auth()->user()->id)
                ->orWhere('type', 0)
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
        $request->validate([
            'subject' => 'required',
            'content' => 'required',
        ], [
            'subject.required' => 'Você deve preencher o campo de assunto',
            'content.numeric' => 'Você deve preencher o campo de mensagem',
        ]);

        try {
            if ($request->user_email) {
                $user_email = User::where('email', $request->user_email)->first();

                $message = Messages::create([
                    'user_id' => $user_email->id,
                    'type' => $request->get('type'),
                    'subject' => $request->get('subject'),
                    'content' => $request->get('content'),
                    'status' => 0
                ]);

                MessageStatus::create([
                    'user_id' => $user_email->id,
                    'message_id' => $message->id,
                    'status' => 0
                ]);
            } else {

                Messages::create([
                    'user_id' => 0,
                    'type' => $request->get('type'),
                    'subject' => $request->get('subject'),
                    'content' => $request->get('content'),
                    'status' => $request->get('status'),
                    'command' => false
                ]);
            }

            return response([
                'message' => 'Mensagem Enviada'
            ], Response::HTTP_OK);

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

            if (!auth()->user()->is_admin) {
                $msg_status = MessageStatus::where([
                    'user_id' => auth()->user()->id,
                    'message_id' => $message_id
                ])->first();

                $msg_status->status = 1;
                $msg_status->save();
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
            ], Response::HTTP_OK);
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

            // Apagando Status
            $msg_status = MessageStatus::where('message_id', $request->id)->get();
            foreach ($msg_status as $status) {
                $status->delete($request->id);
            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Mensagem Apagada!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function userList(Request $request)
    {
        try {
            if ($request->term != null) {
                $users = User::where('email_verified_at', '<>', '')
                    ->where('is_admin', 0)
                    ->where(function ($q) use ($request) {
                        $q->where('name', 'LIKE', "%{$request->term}%")
                            ->orWhere('username', 'LIKE', "%{$request->term}%");
                    })
                    ->orderBy('name')->get();
            } else {
                $users = User::where('email_verified_at', '<>', '')
                    ->where('is_admin', 0)
                    ->limit(1)
                    ->orderBy('name')->get();
            }

            return response($users
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function readed(Request $request)
    {
        try {
            DB::beginTransaction();

            $message = Messages::where('id', $request->id)->first();
            $message->update($request->all());

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Mensagem Atualizada com Sucesso!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public function generalMessages()
    {
        try {
            $messages = Messages::select(['subject', 'content'])->with([
                'statuses' => function ($statuses) {
                    return $statuses->where('user_id', auth()->user()->id);
                }
            ])->where('type', 0)->get();
            return response($messages, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'messages' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function totalMessages()
    {
        try {

            $total = DB::table('message_statuses')
                ->where('user_id', auth()->user()->id)
                ->where(['status' => 0])
                ->count();

            return response($total, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'messages' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public static function createMessageStatus()
    {
        try {
            DB::beginTransaction();

            $users = User::whereNotNull('email_verified_at')->get();
            $messages = Messages::where('command', false)->get();

            foreach ($messages as $message) {
                foreach ($users as $user) {
                    MessageStatus::create([
                        'user_id' => $user->id,
                        'message_id' => $message->id,
                        'status' => 0
                    ]);
                }

                $message->command = true;
                $message->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
