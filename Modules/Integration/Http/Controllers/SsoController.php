<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Integration\Services\SsoService;

class SsoController extends Controller
{
    public function __construct(private readonly SsoService $ssoService)
    {
    }

    public function validateToken(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('integration.sso.validate'), 403);

        $request->validate([
            'token' => ['required', 'string'],
        ]);

        return response()->json($this->ssoService->validateToken($request->string('token')->value()));
    }
}
