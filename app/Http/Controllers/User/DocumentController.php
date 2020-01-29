<?php

namespace App\Http\Controllers\User;

use App\Enum\EnumStatusDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentsRequest;
use App\Models\User\Document;
use App\Models\User\DocumentType;
use App\Services\FileApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function index()
    {
        $document = Document::where('document_type_id', 1)->where('user_id', auth()->user()->id)->first();
        $document_status = $document->status ?? 0;

        $selfie = Document::where('document_type_id', 2)->where('user_id', auth()->user()->id)->first();
        $selfie_status = $selfie->status ?? 0;

        return response([
            'message' => trans('messages.general.success'),
            'document' => [
                'status' => EnumStatusDocument::STATUS[$document_status],
                'message' => EnumStatusDocument::MESSAGE[app()->getLocale()][$document_status]
            ],
            'selfie' => [
                'status' => EnumStatusDocument::STATUS[$selfie_status],
                'message' => EnumStatusDocument::MESSAGE[app()->getLocale()][$selfie_status]
            ],
        ], Response::HTTP_OK);
    }

    /**
     * @param DocumentsRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(DocumentsRequest $request)
    {
        try {
            $extension = $request->file('file')->getClientOriginalExtension();
            $subfolder = auth()->user()->id . "/documents";

            $fileApi = FileApiService::storeFile($request->file('file'), $subfolder);

            $document = Document::firstOrNew([
                "user_id" => auth()->user()->id,
                "document_type_id" => $request->document_type_id
            ]);

            $document->ext = $extension;
            $document->status = EnumStatusDocument::PENDING;
            $document->api_id = $fileApi['id'];
            $document->path = $fileApi['file'];
            $document->save();

            return response([
                'message' => trans('messages.general.success'),
                'document' => $document
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response([
                'message' => trans('messages.documents.sent_error'),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param DocumentsRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function storeS3(DocumentsRequest $request)
    {
        try {
            $type = DocumentType::findOrFail($request->document_type_id);

            $extension = $request->file('file')->getClientOriginalExtension();
            $nameFile = "{$type->type}.{$extension}";
            $path = "liquidex_v2/" . auth()->user()->id . "/documents/";

            $request->file('file')->storeAs($path, $nameFile);

            $document = Document::firstOrNew([
                "user_id" => auth()->user()->id,
                "document_type_id" => $request->document_type_id]);

            $document->ext = $extension;
            $document->status = EnumStatusDocument::PENDING;
            $document->path = $path . $nameFile;
            $document->save();

            return response([
                'message' => trans('messages.general.success'),
                'document' => $document
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response([
                'message' => trans('messages.documents.sent_error'),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function show(Document $document)
    {
        try {
            if ($document->user_id !== auth()->user()->id) {
                throw new \Exception(trans('messages.documents.file_no_longer_available'));
            }

            $expires_at = Carbon::now()->addMinutes(1);
            $url = Storage::disk('s3')->temporaryUrl($document->path, $expires_at);

            return response([
                'message' => trans('messages.general.success'),
                'url' => $url
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function verified($document_type)
    {
        try {
            $type = DocumentType::findOrFail($document_type);

            $document = Document::where('user_id', auth()->user()->id)
                ->where('document_type_id', $type->id)->first();

            if (!$document) {
                return response([
                    'message' => EnumStatusDocument::MESSAGE[app()->getLocale()][EnumStatusDocument::NOTFOUND],
                    'status' => EnumStatusDocument::NOTFOUND,
                ], Response::HTTP_OK);
            }

            return response([
                'message' => EnumStatusDocument::MESSAGE[app()->getLocale()][$document->status],
                'status' => EnumStatusDocument::STATUS[$document->status],
            ], Response::HTTP_OK);


        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
