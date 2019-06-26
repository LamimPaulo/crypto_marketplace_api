<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CanAccess
{
    public function handle($request, Closure $next, $permission)
    {
        $p = PermissionService::permission($permission);

        if (!$p) {
            return response([
                'message' => "Você não tem permissão para acessar os dados requisitados.",
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
