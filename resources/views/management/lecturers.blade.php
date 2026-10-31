@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', config('management.lecturers.title'))
@section('breadcrumb', config('management.lecturers.title'))

@section('content')
    @include('management._table', ['resource' => config('management.lecturers')])
@endsection
