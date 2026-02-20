<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Render admin dashboard summary.
     */
    public function index()
    {
        $role = Auth::user()->role;

        return view('admin.dashboard', [
            'usersCount' => User::query()->count(),
            'categoriesCount' => Category::query()->count(),
            'productsCount' => Product::query()->count(),
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }
}
