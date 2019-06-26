<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CanExecute
{
    public function handle($request, Closure $next, $permission)
    {
        $p = PermissionService::permission($permission);

        if ($p == 2) {
            return $next($request);
        }

        return response([
            'message' => "Você não tem permissão para executar esta ação.",
            'status' => 'error'], Response::HTTP_BAD_REQUEST);

    }
}
