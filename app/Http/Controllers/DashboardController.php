<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $modules = [
            'Auth',
            'User',
            'Course',
            'Programme',
            'Group',
            'Workflow',
            'Examination (JSU)',
            'Notification',
            'Integration (SSO)',
        ];

        return view('dashboard', [
            'modules' => $modules,
        ]);
    }
}
