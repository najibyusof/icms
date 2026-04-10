<?php

namespace Modules\Course\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Course\Http\Requests\StoreCourseRequest;
use Modules\Course\Services\CourseService;

class CourseController extends Controller
{
    public function __construct(private readonly CourseService $courseService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->courseService->list());
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        return response()->json(
            $this->courseService->create($request->validated()),
            201
        );
    }
}
