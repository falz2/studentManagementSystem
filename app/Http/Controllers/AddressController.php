<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('addresses.index', [
            'addresses' => $request->user()->addresses()->latest()->get(),
            'role' => $request->user()->getRoleNames()->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();
        $makeDefault = ($data['is_default'] ?? false) || ! $user->addresses()->exists();

        DB::transaction(function () use ($user, $data, $makeDefault): void {
            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }
            $user->addresses()->create([
                ...$data,
                'is_default' => $makeDefault,
            ]);
        });

        return to_route('addresses.index')->with('success', 'Delivery address saved.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $address = $request->user()->addresses()->findOrFail($address->id);
        $data = $this->validated($request);
        $makeDefault = (bool) ($data['is_default'] ?? false);

        DB::transaction(function () use ($request, $address, $data, $makeDefault): void {
            if ($makeDefault) {
                $request->user()->addresses()->where('id', '<>', $address->id)->update(['is_default' => false]);
            }
            $address->update([...$data, 'is_default' => $makeDefault]);
        });

        return to_route('addresses.index')->with('success', 'Delivery address updated.');
    }

    public function makeDefault(Request $request, Address $address): RedirectResponse
    {
        $address = $request->user()->addresses()->findOrFail($address->id);

        DB::transaction(function () use ($request, $address): void {
            $request->user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return to_route('addresses.index')->with('success', 'Default delivery address updated.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $address = $request->user()->addresses()->findOrFail($address->id);
        $wasDefault = $address->is_default;

        DB::transaction(function () use ($request, $address, $wasDefault): void {
            $address->delete();
            if ($wasDefault) {
                $request->user()->addresses()->latest()->first()?->update(['is_default' => true]);
            }
        });

        return to_route('addresses.index')->with('success', 'Delivery address removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'district' => ['required', 'string', 'max:120'],
            'subcounty' => ['required', 'string', 'max:120'],
            'village' => ['required', 'string', 'max:120'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }
}
