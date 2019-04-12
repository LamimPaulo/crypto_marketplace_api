<?php

namespace App\Http\Middleware;

use App\Models\User\Document;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class DocsCheck
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {

            $cpf = Document::where('document_type_id', 1)->where('status', '=', 1)->where('user_id', '=', auth()->user()->id)->count();
            $selfie = Document::where('document_type_id', 2)->where('status', '=', 1)->where('user_id', '=', auth()->user()->id)->count();

            if ($cpf > 0 AND $selfie > 0) {
                return $next($request);
            }

            throw new \Exception(trans('messages.documents.pending'));
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
