<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        return view('management.students');
    }
}
