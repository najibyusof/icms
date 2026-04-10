<?php

namespace Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Examination\DTOs\SubmitExaminationDTO;
use Modules\Examination\Http\Requests\SubmitExaminationRequest;
use Modules\Examination\Services\ExaminationService;

class ExaminationController extends Controller
{
    public function __construct(private readonly ExaminationService $examinationService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->examinationService->list());
    }

    public function store(SubmitExaminationRequest $request): JsonResponse
    {
        $dto = SubmitExaminationDTO::fromArray($request->validated(), (int) $request->user()->id);

        return response()->json($this->examinationService->submit($dto), 201);
    }
}
