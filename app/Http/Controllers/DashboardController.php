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

        if ($user->hasAnyRole(['farmer', 'supplier'])) {
            return to_route('seller.dashboard');
        }

        if ($user->hasAnyRole(['consumer', 'student', 'lecturer'])) {
            return to_route('marketplace.index');
        }

        abort(403);
    }
}
