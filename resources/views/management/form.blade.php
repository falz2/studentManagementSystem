@extends('layouts.portal')
@php($role = 'admin')

@section('page-title', $title)
@section('breadcrumb', $title)

@section('content')
    <div class="mx-auto max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#d97706]">Administration</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>

        <form method="POST" action="{{ $action }}" class="mt-8 space-y-6 rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            @foreach ($fields as $field)
                @php($fieldType = $field['type'] ?? 'text')
                <div>
                    <label for="{{ $field['name'] }}" class="block text-sm font-medium text-slate-700">{{ $field['label'] }}</label>
                    @if ($fieldType === 'select')
                        <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" @required($field['required'] ?? false) class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($field['options'] as $value => $label)
                                <option value="{{ $value }}" @selected((string) old($field['name'], $field['value'] ?? '') === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        <input
                            id="{{ $field['name'] }}"
                            name="{{ $field['name'] }}"
                            type="{{ $fieldType }}"
                            value="{{ $fieldType === 'password' ? '' : old($field['name'], $field['value'] ?? '') }}"
                            @if (isset($field['min'])) min="{{ $field['min'] }}" @endif
                            @if (isset($field['max'])) max="{{ $field['max'] }}" @endif
                            @if (isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                            @required($field['required'] ?? false)
                            class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    @endif
                    @if (isset($field['hint']))
                        <p class="mt-1 text-xs text-slate-500">{{ $field['hint'] }}</p>
                    @endif
                    @error($field['name'])
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ $cancel }}" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-xl bg-[#2563eb] px-4 py-3 text-sm font-semibold text-white hover:bg-[#1d4ed8]">{{ $submit }}</button>
            </div>
        </form>
    </div>
@endsection
