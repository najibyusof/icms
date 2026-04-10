<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }
}
