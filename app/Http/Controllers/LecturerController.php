<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LecturerController extends Controller
{
    public function index(): View
    {
        return view('management.lecturers');
    }
}
