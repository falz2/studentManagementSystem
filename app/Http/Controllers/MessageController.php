<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Order;
use App\Notifications\MarketplaceAlert;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $conversations = Conversation::query()
            ->with(['order', 'buyer', 'seller', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($query) => $query
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')])
            ->where(fn ($query) => $query->where('buyer_id', $user->id)->orWhere('seller_id', $user->id))
            ->latest('updated_at')
            ->paginate(20);

        return view('messages.index', [
            'conversations' => $conversations,
            'role' => $user->getRoleNames()->first(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $conversation = $this->conversationFor($request, $order);
        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', [
            'conversation' => $conversation->load(['order.items', 'buyer', 'seller', 'messages.sender']),
            'role' => $request->user()->getRoleNames()->first(),
        ]);
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $conversation = $this->conversationFor($request, $order);
        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => trim($data['body']),
        ]);

        $recipient = $request->user()->is($conversation->buyer) ? $conversation->seller : $conversation->buyer;
        $recipient->notify(new MarketplaceAlert(
            'New message about order '.$order->order_number,
            $request->user()->name.': '.\Illuminate\Support\Str::limit($message->body, 120),
            route('messages.show', $order),
            '💬',
        ));

        $conversation->touch();

        return to_route('messages.show', $order)->with('success', 'Message sent.');
    }

    private function conversationFor(Request $request, Order $order): Conversation
    {
        $user = $request->user();
        abort_unless($order->seller_id !== null && ($order->buyer_id === $user->id || $order->seller_id === $user->id), 404);

        return DB::transaction(fn () => Conversation::query()->firstOrCreate(
            ['order_id' => $order->id],
            ['buyer_id' => $order->buyer_id, 'seller_id' => $order->seller_id],
        ));
    }
}
