<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        try {
            $messages = Messages::with(['created_at', 'message'])->get();
            return response([
                'message' => trans('messages.general.success')
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    // Novas Mensagens
    public function store($request)
    {
        $message = new Messages;
        $message->user_id       = $request->user_id;
        $message->type          = $request->type;
        $message->subject       = $request->subject;
        $message->message       = $request->message;
        $message->status        = $request->status;
        $message->save();
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $message = Messages::findOrFail($id);
//        return view('');
    }

    public function update($request, $id)
    {
        $message = Message::findOrFail($id);
        $message->user_id       = $request->user_id;
        $message->type          = $request->type;
        $message->subject       = $request->subject;
        $message->message       = $request->message;
        $message->status        = $request->status;
        $message->save();
    }

    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();
//        return redirect()->route;
    }
}
