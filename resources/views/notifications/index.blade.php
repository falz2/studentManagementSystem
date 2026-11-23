@extends('layouts.portal')

@section('page-title', 'Notifications')

@section('content')
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#4A7C59]">Marketplace updates</p>
            <h1 class="mt-2 text-3xl font-black text-[#2B2B2B]">Notifications</h1>
            <p class="mt-2 text-sm text-[#68695f]">Order, message, and stock updates for your account.</p>
        </div>
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="min-h-12 rounded-xl border border-[#DED5C1] bg-white px-4 text-sm font-extrabold text-[#315D3E]">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="mt-6 space-y-3">
        @forelse ($notifications as $notification)
            <article class="flex items-start gap-3 rounded-[14px] border border-[#e8dfcf] {{ $notification->read_at ? 'bg-white' : 'bg-[#F4F7EF]' }} p-4 shadow-[0_5px_18px_rgba(74,65,41,0.05)]">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#F6F1E5] text-xl" aria-hidden="true">{{ $notification->data['icon'] ?? '🔔' }}</span>
                <div class="min-w-0 flex-1">
                    <h2 class="font-black">{{ $notification->data['title'] ?? 'Marketplace update' }}</h2>
                    <p class="mt-1 text-sm leading-6 text-[#575346]">{{ $notification->data['message'] ?? '' }}</p>
                    <time class="mt-2 block text-xs font-bold text-[#777263]">{{ $notification->created_at->format('M j, Y · g:i A') }}</time>
                </div>
                @if ($notification->read_at)
                    <a href="{{ $notification->data['url'] ?? route('notifications.index') }}" class="flex min-h-12 shrink-0 items-center px-2 text-sm font-extrabold text-[#4A7C59]">Open</a>
                @else
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="min-h-12 shrink-0 rounded-xl bg-[#4A7C59] px-3 text-xs font-extrabold text-white">View</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="rounded-[14px] border border-dashed border-[#D8CFBC] bg-[#FAF7EF] p-10 text-center">
                <p class="text-4xl" aria-hidden="true">🔔</p>
                <h2 class="mt-2 font-black">You're all caught up</h2>
                <p class="mt-1 text-sm text-[#68695f]">New order, message, and inventory alerts will appear here.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-5">{{ $notifications->links() }}</div>
@endsection
