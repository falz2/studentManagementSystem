<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class RegistrationController extends Controller
{
    public function index(): View
    {
        return view('management.registrations');
    }
}
