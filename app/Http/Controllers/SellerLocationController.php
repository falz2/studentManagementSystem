<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SellerLocationController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['nullable', 'required_if:map_visible,1', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_if:map_visible,1', 'numeric', 'between:-180,180'],
            'map_visible' => ['nullable', 'boolean'],
        ]);

        $request->user()->update([
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'map_visible' => (bool) ($data['map_visible'] ?? false),
        ]);

        return to_route('marketplace.map')->with(
            'success',
            $request->boolean('map_visible')
                ? 'Your public farm location is saved.'
                : 'Your farm location is private and no longer appears on the map.',
        );
    }
}
