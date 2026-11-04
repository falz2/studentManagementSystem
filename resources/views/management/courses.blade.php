@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', config('management.courses.title'))
@section('breadcrumb', config('management.courses.title'))

@section('content')
    @include('management._table', [
        'entity' => 'courses',
        'title' => 'Courses',
        'singular' => 'course',
        'description' => 'Course catalogue, capacity, and teaching assignments.',
        'columns' => ['Course', 'Course code', 'Lecturer', 'Schedule', 'Enrolled', 'Status'],
        'records' => $records,
    ])
@endsection
