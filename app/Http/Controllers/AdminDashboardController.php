<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin', [
            'role' => 'admin',
            'productCount' => Product::count(),
            'farmerCount' => User::whereHas('roles', fn ($query) => $query->where('name', 'farmer'))->count(),
            'supplierCount' => User::whereHas('roles', fn ($query) => $query->where('name', 'supplier'))->count(),
            'consumerCount' => User::whereHas('roles', fn ($query) => $query->where('name', 'consumer'))->count(),
            'orderCount' => Order::count(),
            'orders' => Order::with(['buyer', 'seller'])->latest()->limit(8)->get(),
        ]);
    }
}
