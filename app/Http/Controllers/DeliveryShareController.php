<?php

namespace App\Http\Controllers;

use App\Models\DeliveryShare;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DeliveryShareController extends Controller
{
    public function create(Request $request, Order $order): RedirectResponse
    {
        $order = $request->user()->sales()->findOrFail($order->id);
        if ($order->delivery_status !== 'in_transit' || $order->status !== 'confirmed') {
            return back()->with('error', 'Move the order to in transit before creating a rider link.');
        }

        $token = bin2hex(random_bytes(32));

        DB::transaction(function () use ($request, $order, $token): void {
            $request->user()->sales()->lockForUpdate()->findOrFail($order->id);
            $share = DeliveryShare::query()->where('order_id', $order->id)->lockForUpdate()->first()
                ?? new DeliveryShare(['order_id' => $order->id]);
            $share->fill([
                'token_hash' => hash('sha256', $token),
                'latitude' => null,
                'longitude' => null,
                'accuracy_meters' => null,
                'location_updated_at' => null,
                'expires_at' => now()->addHours(12),
                'revoked_at' => null,
            ])->save();
        });

        return to_route('seller.orders')->with([
            'success' => 'Private rider link created. It expires in 12 hours or when delivery is completed.',
            'delivery_share_url' => route('delivery-share.show').'#'.$token,
        ]);
    }

    public function revoke(Request $request, Order $order): RedirectResponse
    {
        $order = $request->user()->sales()->findOrFail($order->id);
        $share = $order->deliveryShare()->firstOrFail();
        $share->update([
            'revoked_at' => now(),
            'latitude' => null,
            'longitude' => null,
            'accuracy_meters' => null,
            'location_updated_at' => null,
        ]);

        return to_route('seller.orders')->with('success', 'Rider link revoked and saved GPS location cleared.');
    }

    public function showPublic(): Response
    {
        return response()
            ->view('delivery-share')
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function orderLocation(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        abort_unless($order->buyer_id === $user->id || $order->seller_id === $user->id, 404);

        $share = $order->deliveryShare;
        $location = $share?->isActive() && $share->location_updated_at
            ? $this->locationData($share)
            : null;

        return response()->json([
            'active' => (bool) $share?->isActive(),
            'location' => $location,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function updateOrPoll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/i'],
            'latitude' => ['sometimes', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'between:0,10000'],
        ]);
        $share = DeliveryShare::query()
            ->with('order')
            ->where('token_hash', hash('sha256', strtolower($data['token'])))
            ->firstOrFail();

        if (! $share->isActive()) {
            return response()->json(['active' => false, 'location' => null], 410)
                ->header('Cache-Control', 'no-store, private');
        }

        if (array_key_exists('latitude', $data) && array_key_exists('longitude', $data)) {
            $share->update([
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy_meters' => $data['accuracy_meters'] ?? null,
                'location_updated_at' => now(),
            ]);
        }

        return response()->json([
            'active' => true,
            'location' => $share->location_updated_at ? $this->locationData($share->fresh()) : null,
        ])->header('Cache-Control', 'no-store, private');
    }

    private function locationData(DeliveryShare $share): array
    {
        return [
            'latitude' => $share->latitude,
            'longitude' => $share->longitude,
            'accuracy_meters' => $share->accuracy_meters,
            'updated_at' => $share->location_updated_at?->toIso8601String(),
        ];
    }
}
