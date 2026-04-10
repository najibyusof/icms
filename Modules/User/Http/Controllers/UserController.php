<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\User\Services\UserService;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function index(): JsonResponse
    {
        abort_unless(auth()->user()?->can('user.view'), 403);

        return response()->json($this->userService->paginated());
    }
}
