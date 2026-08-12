<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    public function __construct(protected WishlistService $wishlist) {}

    public function index()
    {
        return view('site.wishlist.index', ['items' => $this->wishlist->content()]);
    }

    public function toggle(Product $product): JsonResponse
    {
        $inWishlist = $this->wishlist->toggle($product);

        return response()->json([
            'success' => true,
            'in_wishlist' => $inWishlist,
            'count' => $this->wishlist->count(),
        ]);
    }
}
