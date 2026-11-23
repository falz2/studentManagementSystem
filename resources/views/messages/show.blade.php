@extends('layouts.portal')

@section('page-title', 'Order messages')

@section('content')
    @php($otherPerson = auth()->id() === $conversation->buyer_id ? $conversation->seller : $conversation->buyer)
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('messages.index') }}" class="inline-flex min-h-12 items-center font-extrabold text-[#4A7C59]">← All messages</a>
        <section class="mt-3 overflow-hidden rounded-[14px] border border-[#e8dfcf] bg-white shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
            <header class="border-b border-[#e8dfcf] bg-[#F6F2E8] p-4 sm:p-5">
                <p class="text-xs font-extrabold uppercase tracking-wide text-[#4A7C59]">Order {{ $conversation->order->order_number }}</p>
                <h1 class="mt-1 text-lg font-black">Chat with {{ $otherPerson->name }}</h1>
                <p class="mt-1 text-sm text-[#68695f]">Order total · UGX {{ number_format($conversation->order->total) }}</p>
            </header>
            <div class="max-h-[55vh] space-y-4 overflow-y-auto p-4 sm:p-6" aria-live="polite">
                @forelse ($conversation->messages as $message)
                    <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <article class="max-w-[88%] rounded-2xl {{ $message->sender_id === auth()->id() ? 'rounded-br-sm bg-[#4A7C59] text-white' : 'rounded-bl-sm bg-[#F2EEE3] text-[#2B2B2B]' }} px-4 py-3">
                            <p class="text-xs font-extrabold opacity-75">{{ $message->sender->name }}</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-6">{{ $message->body }}</p>
                            <time class="mt-2 block text-right text-[10px] opacity-70">{{ $message->created_at->format('M j · g:i A') }}</time>
                        </article>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-[#68695f]">No messages yet. Send a note about this order.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('messages.store', $conversation->order) }}" class="flex flex-col gap-2 border-t border-[#e8dfcf] p-3 sm:flex-row">
                @csrf
                <label for="message-body" class="sr-only">Write a message</label>
                <textarea id="message-body" name="body" rows="2" required maxlength="2000" placeholder="Write a message…" class="min-h-12 min-w-0 flex-1 resize-y rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">{{ old('body') }}</textarea>
                <button class="min-h-12 rounded-xl bg-[#4A7C59] px-5 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Send message</button>
            </form>
        </section>
    </div>
@endsection
