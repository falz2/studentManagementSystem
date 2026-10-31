@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', config('management.registrations.title'))
@section('breadcrumb', config('management.registrations.title'))

@section('content')
    @include('management._table', ['resource' => config('management.registrations')])
@endsection
