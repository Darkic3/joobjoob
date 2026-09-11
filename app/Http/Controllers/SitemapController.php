<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()->where('is_active', true)->select(['slug', 'updated_at'])->latest('updated_at')->get();
        $categories = ProductCategory::query()->where('is_active', true)->select(['slug', 'updated_at'])->latest('updated_at')->get();
        $content = view('sitemap.index', compact('products', 'categories'));
        return response($content, 200)->header('Content-Type', 'text/xml');
    }
}
