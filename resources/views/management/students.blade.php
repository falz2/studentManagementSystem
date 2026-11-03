@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', config('management.students.title'))
@section('breadcrumb', config('management.students.title'))

@section('content')
    @include('management._table', [
        'entity' => 'students',
        'title' => 'Students',
        'singular' => 'student',
        'description' => 'Student profiles and academic details.',
        'columns' => ['Student', 'Student ID', 'Email', 'Programme', 'Year', 'Status'],
        'records' => $records,
    ])
@endsection
