<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('seller')
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhereHas('seller', fn ($seller) => $seller->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when(in_array($request->input('category'), ['poultry', 'feed'], true), fn ($query) => $query->where('category', $request->input('category')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.index', [
            'role' => $request->user()->getRoleNames()->first(),
            'products' => $products,
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->status === 'active' && ! $product->isExpired(), 404);
        $product->load(['seller', 'reviews.buyer']);
        $product->loadAvg('reviews', 'rating')->loadCount('reviews');

        return view('marketplace.show', [
            'role' => $request->user()->getRoleNames()->first(),
            'product' => $product,
            'reviewableItems' => OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', fn ($query) => $query
                    ->where('buyer_id', $request->user()->id)
                    ->where('status', 'completed'))
                ->whereDoesntHave('review')
                ->with('order')
                ->get(),
        ]);
    }
}
