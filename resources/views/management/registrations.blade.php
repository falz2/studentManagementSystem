@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', config('management.registrations.title'))
@section('breadcrumb', config('management.registrations.title'))

@section('content')
    @include('management._table', [
        'entity' => 'registrations',
        'title' => 'Registrations',
        'singular' => 'registration',
        'description' => 'Student enrolments and their current registration status.',
        'columns' => ['Student', 'Student ID', 'Course', 'Course code', 'Registered', 'Status'],
        'records' => $records,
    ])
@endsection
