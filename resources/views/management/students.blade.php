@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', config('management.students.title'))
@section('breadcrumb', config('management.students.title'))

@section('content')
    @include('management._table', ['resource' => config('management.students')])
@endsection
