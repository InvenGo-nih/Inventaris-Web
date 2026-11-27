<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        try {
            $allowed = $user->hasAnyPermission($permissions);
        } catch (\Throwable $e) {
            $allowed = false;
        }

        if (! $allowed) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman tersebut');
        }

        return $next($request);
    }
}
