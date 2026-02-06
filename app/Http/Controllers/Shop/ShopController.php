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
        // Fetch categories that have ACTIVE items
        $categories = Category::whereHas('items', function ($query) {
            $query->active();
        })->withCount(['items' => function ($query) {
            $query->active();
        }])->get();

        // Base query for active items
        $query = Item::active()->with('category');

        // Filter by category
        $query->when($request->category, function ($q, $categoryName) {
            return $q->whereHas('category', function ($sq) use ($categoryName) {
                $sq->where('name', $categoryName);
            });
        });

        $query->when($request->category_id, function ($q, $categoryId) {
            // Validate UUID if using UUIDs
            if (!\Illuminate\Support\Str::isUuid($categoryId)) {
                 // Return empty result for invalid UUID format
                 return $q->whereRaw('1 = 0');
            }
            return $q->where('category_id', $categoryId);
        });

        // Search
        $query->when($request->search, function ($q, $search) {
            $q->where('name', 'ilike', '%' . $search . '%');
        });

        // Pagination
        $items = $query->orderBy('name')->paginate(12);

        return view('shop.index', compact('items', 'categories'));
    }
}
