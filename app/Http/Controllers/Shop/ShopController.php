<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display the homepage with products
     */
    public function index(Request $request)
    {
        // Fetch categories that have items
        $categories = Category::has('items')->withCount(['items' => function ($query) {
            $query->active();
        }])->get();

        // Base query for active items
        $query = Item::active()->with('category');

        // Filter by category
        $query->when($request->category_id, function ($q, $categoryId) {
            return $q->where('category_id', $categoryId);
        });

        // Search
        if ($request->has('q') && $request->q) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // Pagination
        $items = $query->orderBy('name')->paginate(12);

        return view('shop.index', compact('items', 'categories'));
    }
}
