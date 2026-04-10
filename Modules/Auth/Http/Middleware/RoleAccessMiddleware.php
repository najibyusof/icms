<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleAccessMiddleware
{
    /**
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $normalizedRoles = array_filter(array_map(static fn (string $role): string => trim($role), $roles));

        if ($normalizedRoles !== [] && ! $user->hasAnyRole($normalizedRoles)) {
            abort(403);
        }

        return $next($request);
    }
}
