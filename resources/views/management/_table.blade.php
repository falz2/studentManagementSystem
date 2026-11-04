<div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#d97706]">Administration</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>
    </div>
    <a href="{{ route('admin.'.$entity.'.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#2563eb] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]">+ Add {{ $singular }}</a>
</div>

@if (session('success'))
    <p role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</p>
@endif

<section class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="border-b border-slate-100 p-5 sm:px-6">
        <h2 class="text-sm font-bold text-slate-900">All {{ strtolower($title) }}</h2>
        <p class="mt-1 text-xs text-slate-400">{{ $records->total() }} records</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[740px] text-left">
            <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                <tr>
                    @foreach ($columns as $column)
                        <th class="px-5 py-3 first:pl-6">{{ $column }}</th>
                    @endforeach
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse ($records as $record)
                    <tr class="hover:bg-slate-50">
                        @foreach ($record['cells'] as $value)
                            <td class="whitespace-nowrap px-5 py-4 first:pl-6 {{ $loop->first ? 'font-semibold text-slate-700' : 'text-slate-500' }}">{{ $value }}</td>
                        @endforeach
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <a href="{{ route('admin.'.$entity.'.edit', $record['id']) }}" class="font-semibold text-[#2563eb] hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.'.$entity.'.destroy', $record['id']) }}" class="ml-3 inline" onsubmit="return confirm('Delete this {{ $singular }}? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-semibold text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 1 }}" class="px-6 py-12 text-center text-sm text-slate-500">No {{ strtolower($title) }} have been added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($records->hasPages())
        <div class="border-t border-slate-100 px-6 py-4">{{ $records->links() }}</div>
    @endif
</section>
