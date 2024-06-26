<?php

namespace App\Http\Controllers\User;

use App\Enum\EnumUserTicketsDepartments;
use App\Enum\EnumUserTicketsStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserTicketMessageRequest;
use App\Http\Requests\UserTicketRequest;
use App\Models\User\UserTicketMessage;
use App\Models\User\UserTicket;
use App\Models\SupportConfig;
use App\Services\FileApiService;
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
                    return $messages->with('files', 'user');
                }
            ])->where('user_id', auth()->user()->id)
                ->orderBy('status')
                ->orderBy('created_at')->paginate(10);

            return response($tickets
                , Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => "Erro ao carregar tickets: {$e->getMessage()}"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(UserTicketRequest $request)
    {
        try {
            $tickets = UserTicket::where('user_id', auth()->user()->id)
                ->whereIn('status', [EnumUserTicketsStatus::PENDING, EnumUserTicketsStatus::WAIT_USER])->first();

            if ($tickets) {
                throw new \Exception('Não é possivel abrir um novo ticket enquanto há um pendente');
            }

            DB::beginTransaction();

            $request['user_id'] = auth()->user()->id;
            $request['status'] = EnumUserTicketsStatus::PENDING;

            $ticket = UserTicket::create($request->all());

            $message = UserTicketMessage::create([
                'message' => $request->message,
                'user_id' => $ticket->user_id,
                'user_ticket_id' => $ticket->id,
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
                $file = $this->uploadFile($request);
                $extension = $request->file('file')->getClientOriginalExtension();
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

    public function config()
    {
        try {
            $config = SupportConfig::first();

            return response([
                'config' => $config
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
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

    public function departments()
    {
        return EnumUserTicketsDepartments::DEPARTMENT;
    }

}
