<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->withCount('products')->take(8)->get();
        $featured = Product::with(['images', 'category'])->where('is_featured', true)->take(8)->get();
        $bestSellers = Product::with(['images', 'category'])->where('is_best_seller', true)->take(8)->get();
        $deals = Product::with('images')->whereNotNull('deal_ends_at')->where('deal_ends_at', '>', now())->take(4)->get();
        $reviews = \App\Models\Review::with(['user', 'product'])->latest()->take(6)->get();

        return view('home', compact('categories', 'featured', 'bestSellers', 'deals', 'reviews'));
    }
}
