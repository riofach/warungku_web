<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::whereHas('items', function ($query) {
            $query->active();
        })->withCount(['items' => function ($query) {
            $query->active();
        }])->get();

        $query = Item::active()->with(['category', 'activeUnits']);

        $query->when($request->category, function ($q, $categoryName) {
            return $q->whereHas('category', function ($sq) use ($categoryName) {
                $sq->where('name', $categoryName);
            });
        });

        $query->when($request->category_id, function ($q, $categoryId) {
            if (!\Illuminate\Support\Str::isUuid($categoryId)) {
                return $q->whereRaw('1 = 0');
            }
            return $q->where('category_id', $categoryId);
        });

        $query->when($request->search, function ($q, $search) {
            $q->where('name', 'ilike', '%' . $search . '%');
        });

        $items = $query->orderBy('name')->paginate(12);

        return view('shop.index', compact('items', 'categories'));
    }
}
