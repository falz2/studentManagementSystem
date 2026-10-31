<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return match ($request->user()->role) {
            'admin' => to_route('admin.dashboard'),
            'lecturer' => to_route('lecturer.dashboard'),
            'student' => to_route('student.dashboard'),
            default => abort(403),
        };
    }
}
