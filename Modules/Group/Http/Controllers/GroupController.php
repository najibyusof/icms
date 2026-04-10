<?php

namespace Modules\Group\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Group\Http\Requests\StoreGroupRequest;
use Modules\Group\Services\GroupService;

class GroupController extends Controller
{
    public function __construct(private readonly GroupService $groupService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->groupService->list());
    }

    public function store(StoreGroupRequest $request): JsonResponse
    {
        return response()->json(
            $this->groupService->create($request->validated()),
            201
        );
    }
}
