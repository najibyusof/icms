<?php

namespace Modules\Programme\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Programme\Http\Requests\StoreProgrammeRequest;
use Modules\Programme\Services\ProgrammeService;

class ProgrammeController extends Controller
{
    public function __construct(private readonly ProgrammeService $programmeService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->programmeService->list());
    }

    public function store(StoreProgrammeRequest $request): JsonResponse
    {
        return response()->json(
            $this->programmeService->create($request->validated()),
            201
        );
    }
}
