@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', config('management.courses.title'))
@section('breadcrumb', config('management.courses.title'))

@section('content')
    @include('management._table', ['resource' => config('management.courses')])
@endsection
