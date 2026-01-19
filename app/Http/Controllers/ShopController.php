<?php

namespace App\Http\Controllers;

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
        $categories = Category::withCount(['items' => function ($query) {
            $query->active();
        }])->get();

        $query = Item::active()->with('category');

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $items = $query->orderBy('name')->get();

        return view('shop.index', compact('items', 'categories'));
    }

    /**
     * Display item detail
     */
    public function show(Item $item)
    {
        return view('shop.show', compact('item'));
    }
}
