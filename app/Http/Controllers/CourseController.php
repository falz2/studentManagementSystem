<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        return view('management.courses');
    }
}
