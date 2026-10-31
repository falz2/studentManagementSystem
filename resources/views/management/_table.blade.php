<div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#d97706]">Administration</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $resource['title'] }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ $resource['description'] }}</p>
    </div>
    <span class="inline-flex items-center gap-2 self-start rounded-xl bg-[#2563eb] px-4 py-3 text-sm font-semibold text-white sm:self-auto">+ Add {{ $resource['singular'] }}</span>
</div>

<section class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h2 class="text-sm font-bold text-slate-900">All {{ strtolower($resource['title']) }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ count($resource['records']) }} example records · Fall 2026</p>
        </div>
        <label class="relative block sm:w-64">
            <span class="sr-only">Search {{ strtolower($resource['title']) }}</span>
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m16 16 4 4"/></svg>
            <input type="search" data-table-search placeholder="Search {{ strtolower($resource['title']) }}..." class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-xs outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-50">
        </label>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[740px] text-left">
            <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                <tr>
                    @foreach ($resource['columns'] as $column)
                        <th class="px-5 py-3 first:pl-6">{{ $column }}</th>
                    @endforeach
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @foreach ($resource['records'] as $record)
                    <tr data-searchable-row class="hover:bg-slate-50">
                        @foreach ($record as $value)
                            @if ($loop->first)
                                <td class="whitespace-nowrap px-5 py-4 pl-6 font-semibold text-slate-700">{{ $value }}</td>
                            @elseif ($loop->last)
                                <td class="px-5 py-4"><span class="status-pill {{ in_array(strtolower($value), ['active', 'open', 'confirmed'], true) ? 'status-active' : (in_array(strtolower($value), ['pending', 'almost full', 'waitlisted'], true) ? 'status-pending' : 'status-neutral') }}">{{ $value }}</span></td>
                            @else
                                <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $value }}</td>
                            @endif
                        @endforeach
                        <td class="px-6 py-4 text-right"><button type="button" aria-label="More actions for {{ $record[array_key_first($record)] }}" class="rounded-lg px-2 py-1 text-base text-slate-400 hover:bg-slate-100 hover:text-slate-700">···</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div data-empty-state class="hidden border-t border-slate-100 px-6 py-12 text-center">
        <p class="text-sm font-semibold text-slate-600">No matching records</p>
        <p class="mt-1 text-xs text-slate-400">Try another search term.</p>
    </div>
    <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
        <span class="text-xs text-slate-400">Showing {{ count($resource['records']) }} example records</span>
        <div class="flex gap-2"><button type="button" disabled class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-300">Previous</button><button type="button" disabled class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-300">Next</button></div>
    </div>
</section>

<p class="mt-5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs leading-5 text-slate-600">These screens use example records. Database editing is not connected yet.</p>
