<?php

namespace App\Http\Controllers\Admin\User;

use App\Enum\EnumUserTicketsStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserTicketMessageRequest;
use App\Models\User\UserTicket;
use App\Models\User\UserTicketMessage;
use App\Services\FileApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class UserTicketController extends Controller
{
    public function index()
    {
        try {
            $tickets = UserTicket::with([
                'messages' => function ($messages) {
                    return $messages->with('files');
                }
            ])
                ->orderBy('status')
                ->orderBy('created_at')->paginate(10);

            return response($tickets
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro ao caregar tickets: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function byDepartment(Request $request)
    {
        try {
            $tickets = UserTicket::with([
                'messages' => function ($messages) {
                    return $messages->with(['files', 'user']);
                },
                'user'
            ])
                ->where('department', $request->department)
                ->orderBy('status')
                ->orderBy('created_at');

            if (!empty($request->term)) {
                $tickets->whereHas('user', function ($user) use ($request) {
                    return $user->where('name', 'LIKE', "%{$request->term}%")
                        ->orWhere('username', 'LIKE', "%{$request->term}%")
                        ->orWhere('email', 'LIKE', "%{$request->term}%");
                });
            }

            if (!empty($request->id)) {
                $tickets->where('id', $request->id);
            }

            return response($tickets->paginate(10), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro ao caregar tickets: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function byStatus(Request $request)
    {
        try {
            $tickets = UserTicket::with([
                'messages' => function ($messages) {
                    return $messages->with(['files', 'user']);
                },
                'user'
            ])
                ->where('status', $request->status)
                ->orderBy('status')
                ->orderBy('created_at');

            if (!empty($request->term)) {
                $tickets->whereHas('user', function ($user) use ($request) {
                    return $user->where('name', 'LIKE', "%{$request->term}%")
                        ->orWhere('username', 'LIKE', "%{$request->term}%")
                        ->orWhere('email', 'LIKE', "%{$request->term}%");
                });
            }

            if (!empty($request->id)) {
                $tickets->where('id', $request->id);
            }

            return response($tickets->paginate(10), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro ao caregar tickets: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function storeMessage(UserTicketMessageRequest $request)
    {
        try {
            DB::beginTransaction();

            $ticket = UserTicket::find($request->user_ticket_id);
            $ticket->status = $request->status;
            $ticket->save();

            $message = UserTicketMessage::create([
                'message' => $request->message,
                'user_id' => auth()->user()->id,
                'user_ticket_id' => $ticket->id,
                'status' => $request->status
            ]);

            if ($request->hasFile('file')) {
                $extension = $request->file('file')->getClientOriginalExtension();
                $file = $this->uploadFile($request);
                $message->files()->create([
                    'file' => $file['file'],
                    'api_id' => $file['id'],
                    'type' => $extension,
                ]);
            }

            DB::commit();

            return response([
                'message' => trans('messages.general.success'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function uploadFile($request)
    {
        try {
            $subfolder = auth()->user()->id . "/tickets";
            $fileApi = FileApiService::storeFile($request->file('file'), $subfolder);

            return $fileApi;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function uploadFileS3($request)
    {
        try {
            $uuid4 = Uuid::uuid4()->toString();
            $extension = $request->file('file')->getClientOriginalExtension();
            $nameFile = $uuid4 . ".{$extension}";
            $request->file('file')->storeAs("liquidex_v2/" . auth()->user()->id . "/tickets/", $nameFile);
            $file_path = "liquidex_v2/" . auth()->user()->id . "/tickets/$nameFile";
            return $file_path;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function status()
    {
        return EnumUserTicketsStatus::STATUS;
    }
}
