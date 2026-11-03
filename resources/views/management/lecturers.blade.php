@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', config('management.lecturers.title'))
@section('breadcrumb', config('management.lecturers.title'))

@section('content')
    @include('management._table', [
        'entity' => 'lecturers',
        'title' => 'Lecturers',
        'singular' => 'lecturer',
        'description' => 'Teaching staff and their current course assignments.',
        'columns' => ['Lecturer', 'Staff ID', 'Email', 'Department', 'Courses', 'Status'],
        'records' => $records,
    ])
@endsection
