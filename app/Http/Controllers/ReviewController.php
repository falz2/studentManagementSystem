<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Notifications\MarketplaceAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'order_item_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $item = OrderItem::query()
            ->whereKey($data['order_item_id'])
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->where('status', 'completed'))
            ->whereDoesntHave('review')
            ->firstOrFail();

        $product->reviews()->create([
            'order_item_id' => $item->id,
            'buyer_id' => $request->user()->id,
            'rating' => $data['rating'],
            'body' => $data['body'] ?? null,
        ]);

        $product->seller?->notify(new MarketplaceAlert(
            'New product review',
            $request->user()->name.' left a '.$data['rating'].'-star review for '.$product->name.'.',
            route('marketplace.show', $product),
            '⭐',
        ));

        return back()->with('success', 'Thank you for reviewing this product.');
    }
}
