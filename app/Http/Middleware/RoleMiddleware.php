<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $userRole = strtolower($request->user()->role);

        $roles = array_map('strtolower', $roles);

        if (!in_array($userRole, $roles)) {
            abort(403, 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }

        return $next($request);
    }

}
