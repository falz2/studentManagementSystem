@extends('layouts.portal')

@section('page-title', 'Messages')

@section('content')
    <div>
        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#4A7C59]">Buyer and seller conversations</p>
        <h1 class="mt-2 text-3xl font-black text-[#2B2B2B]">Messages</h1>
        <p class="mt-2 text-sm text-[#68695f]">Each conversation is linked to a marketplace order.</p>
    </div>
    <div class="mt-6 space-y-3">
        @forelse ($conversations as $conversation)
            @php($otherPerson = auth()->id() === $conversation->buyer_id ? $conversation->seller : $conversation->buyer)
            <a href="{{ route('messages.show', $conversation->order) }}" class="flex min-h-24 items-center gap-4 rounded-[14px] border border-[#e8dfcf] bg-white p-4 shadow-[0_5px_18px_rgba(74,65,41,0.05)] transition hover:border-[#4A7C59]">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#EAF2E6] text-lg font-black text-[#315D3E]">{{ str($otherPerson->name)->substr(0, 1)->upper() }}</span>
                <span class="min-w-0 flex-1">
                    <span class="flex flex-wrap items-center gap-2 font-black">{{ $otherPerson->name }} <span class="rounded-full bg-[#F6F1E5] px-2 py-0.5 text-xs font-bold text-[#575346]">{{ $conversation->order->order_number }}</span></span>
                    <span class="mt-1 block truncate text-sm text-[#68695f]">{{ $conversation->latestMessage?->body ?? 'Start a conversation about this order.' }}</span>
                    <span class="mt-1 block text-xs font-bold text-[#777263]">{{ $conversation->updated_at->format('M j · g:i A') }}</span>
                </span>
                @if ($conversation->unread_count > 0)
                    <span class="flex h-7 min-w-7 items-center justify-center rounded-full bg-[#4A7C59] px-2 text-xs font-black text-white">{{ $conversation->unread_count }}</span>
                @else
                    <span class="text-lg font-black text-[#4A7C59]" aria-hidden="true">→</span>
                @endif
            </a>
        @empty
            <div class="rounded-[14px] border border-dashed border-[#D8CFBC] bg-[#FAF7EF] p-10 text-center">
                <p class="text-4xl" aria-hidden="true">💬</p>
                <h2 class="mt-2 font-black">No messages yet</h2>
                <p class="mt-1 text-sm text-[#68695f]">You can message the other person from an order.</p>
                <a href="{{ route('orders.index') }}" class="mt-4 inline-flex min-h-12 items-center font-extrabold text-[#4A7C59]">View orders</a>
            </div>
        @endforelse
    </div>
    <div class="mt-5">{{ $conversations->links() }}</div>
@endsection
