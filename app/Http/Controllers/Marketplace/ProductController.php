<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * GET /api/marketplace/products
     *
     * Query params:
     *  - category: id or slug (optional)
     *  - q: search text (title/description)
     *  - condition: new|used
     *  - price_min, price_max
     *  - sort: newest | price_asc | price_desc
     *  - per_page (default 20)
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category' => 'nullable',
            'q' => 'nullable|string|max:255',
            'condition' => 'nullable|in:new,used',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'sort' => 'nullable|in:newest,price_asc,price_desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 20;

        $query = Product::with(['category','vendor'])->publiclyVisible();

        // category can be id or slug
        if (!empty($validated['category'])) {
            $category = $validated['category'];
            if (is_numeric($category)) {
                $query->where('category_id', (int)$category);
            } else {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            }
        }

        if (!empty($validated['q'])) {
            $q = $validated['q'];
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if (!empty($validated['condition'])) {
            $query->where('condition', $validated['condition']);
        }

        if (isset($validated['price_min'])) {
            $query->where('price', '>=', $validated['price_min']);
        }

        if (isset($validated['price_max'])) {
            $query->where('price', '<=', $validated['price_max']);
        }

        // sorting
        switch ($validated['sort'] ?? 'newest') {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }
}
