<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return to_route('admin.dashboard');
        }

        if ($user->hasRole('lecturer')) {
            return to_route('lecturer.dashboard');
        }

        if ($user->hasRole('student')) {
            return to_route('student.dashboard');
        }

        abort(403);
    }
}
