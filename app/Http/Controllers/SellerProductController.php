<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\InventoryAlertService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SellerProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()->products()->latest()->paginate(12);

        return view('seller.products.index', [
            'role' => $request->user()->getRoleNames()->first(),
            'products' => $products,
            'lowStockCount' => $request->user()->products()->where('status', 'active')->whereColumn('quantity', '<=', 'low_stock_threshold')->count(),
            'expiringProducts' => $request->user()->products()
                ->where('status', 'active')
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '<=', today()->addDays(30))
                ->orderBy('expires_at')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->form($request, new Product, 'Add a product', route('seller.products.store'), 'POST');
    }

    public function store(Request $request, InventoryAlertService $inventoryAlerts): RedirectResponse
    {
        $data = $this->validated($request);
        $data['seller_id'] = $request->user()->id;
        $data['image_path'] = $request->file('image')?->store('products', 'public');
        unset($data['image']);
        $product = $request->user()->products()->create($data);
        $inventoryAlerts->notifyFor($product);

        return to_route('seller.products.index')->with('success', 'Product listed on the marketplace.');
    }

    public function edit(Request $request, Product $product): View
    {
        $product = $request->user()->products()->findOrFail($product->id);

        return $this->form($request, $product, 'Edit product', route('seller.products.update', $product), 'PUT');
    }

    public function update(Request $request, Product $product, InventoryAlertService $inventoryAlerts): RedirectResponse
    {
        $product = $request->user()->products()->findOrFail($product->id);
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);
        $product->update($data);
        $inventoryAlerts->notifyFor($product->fresh());

        return to_route('seller.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $product = $request->user()->products()->findOrFail($product->id);
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return to_route('seller.products.index')->with('success', 'Product removed from the marketplace.');
    }

    private function validated(Request $request): array
    {
        $categories = $request->user()->isSupplier() ? ['feed'] : ['poultry', 'feed'];

        return $request->validate([
            'category' => ['required', Rule::in($categories)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'low_stock_threshold' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'unit' => ['required', Rule::in(['bird', 'kg', 'tray', 'bag'])],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function form(Request $request, Product $product, string $title, string $action, string $method): View
    {
        return view('seller.products.form', [
            'role' => $request->user()->getRoleNames()->first(),
            'product' => $product,
            'title' => $title,
            'action' => $action,
            'method' => $method,
            'categories' => $request->user()->isSupplier()
                ? ['feed' => 'Poultry feed']
                : ['poultry' => 'Poultry products', 'feed' => 'Poultry feed'],
        ]);
    }
}
