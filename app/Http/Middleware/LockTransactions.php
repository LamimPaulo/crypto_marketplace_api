<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class LockTransactions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        try {
            throw new \Exception("Esta funcionalidade não está disponível no momento.");
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
