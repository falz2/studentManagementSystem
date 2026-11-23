<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FarmMapController extends Controller
{
    public function index(Request $request): View
    {
        $locations = User::role(['farmer', 'supplier'])
            ->where('map_visible', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('roles:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude'])
            ->map(fn (User $seller): array => [
                'name' => $seller->name,
                'type' => $seller->isSupplier() ? 'Feed supplier' : 'Poultry farm',
                'latitude' => $seller->latitude,
                'longitude' => $seller->longitude,
            ])
            ->values();

        return view('marketplace.map', [
            'locations' => $locations,
            'role' => $request->user()->getRoleNames()->first(),
            'seller' => in_array($request->user()->getRoleNames()->first(), ['farmer', 'supplier'], true)
                ? $request->user()
                : null,
        ]);
    }
}
