<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Integration\Services\SsoService;

class SsoController extends Controller
{
    public function __construct(private readonly SsoService $ssoService)
    {
    }

    public function redirect(): RedirectResponse
    {
        abort_unless((bool) config('services.sso.enabled'), 404);

        return $this->ssoService->redirectToProvider();
    }

    public function settings(Request $request): View
    {
        abort_unless($request->user()?->hasAnyRole(['Admin', 'admin']), 403);

        return view('integration.sso-settings', [
            'settings' => $this->ssoService->settings(),
            'availableRoles' => ['Admin', 'Lecturer', 'Programme Coordinator', 'Reviewer', 'Approver'],
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['Admin', 'admin']), 403);

        $validated = $request->validate([
            'default_role' => ['required', 'string', 'max:100'],
            'external_roles' => ['nullable', 'array'],
            'external_roles.*' => ['nullable', 'string', 'max:100'],
            'local_roles' => ['nullable', 'array'],
            'local_roles.*' => ['nullable', 'string', 'max:100'],
        ]);

        $roleMap = [];

        foreach (($validated['external_roles'] ?? []) as $index => $externalRole) {
            $externalRole = strtolower(trim((string) $externalRole));
            $localRole = trim((string) (($validated['local_roles'] ?? [])[$index] ?? ''));

            if ($externalRole !== '' && $localRole !== '') {
                $roleMap[$externalRole] = $localRole;
            }
        }

        $this->ssoService->saveSettings([
            'default_role' => $validated['default_role'],
            'role_map' => $roleMap,
        ]);

        return redirect()
            ->route('integration.sso.settings')
            ->with('success', 'SSO settings updated successfully.');
    }

    public function callback(): RedirectResponse
    {
        abort_unless((bool) config('services.sso.enabled'), 404);

        $this->ssoService->handleCallback();

        return redirect()->route('dashboard');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user()?->only(['id', 'name', 'email', 'staff_id', 'faculty']),
            'roles' => $request->user()?->roles()->pluck('name')->values()->all() ?? [],
        ]);
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
