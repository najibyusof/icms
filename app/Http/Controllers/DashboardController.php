<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function __invoke(): View
    {
        $overview = $this->dashboardService->getOverview();

        return view('dashboard', [
            'overview' => $overview,
        ]);
    }
}
