<?php

namespace Modules\Integration\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSsoAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((bool) config('services.sso.enabled'), 404);

        if (! $request->user()) {
            return redirect()->route('integration.sso.redirect');
        }

        return $next($request);
    }
}
