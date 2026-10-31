<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin', ['role' => 'admin']);
    }
}
