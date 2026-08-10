<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeroSlideSearchController extends Controller
{
    public function searchModel(Request $request): JsonResponse
    {
        $type = $request->input('type');
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = match ($type) {
            'product' => $this->searchProducts($query),
            'product_category' => $this->searchProductCategories($query),
            'blog' => $this->searchBlogs($query),
            'blog_category' => $this->searchBlogCategories($query),
            default => [],
        };

        return response()->json($results);
    }

    private function searchProducts(string $query): array
    {
        $searchTerm = '%'.$query.'%';
        $products = Product::active()
            ->where(function ($q) use ($searchTerm) {
                $q->where('name->fa', 'like', $searchTerm)
                    ->orWhere('name->en', 'like', $searchTerm);
            })
            ->limit(10)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'title' => $product->trans('name'),
                'subtitle' => number_format($product->final_price).' تومان',
                'image' => $product->main_image_url,
                // قیمت‌ها برای پر شدن خودکار فیلدهای هیرو از محصول
                'price' => (int) $product->final_price,
                'old_price' => (int) ($product->price > $product->final_price ? $product->price : 0),
            ]);

        return $products->toArray();
    }

    private function searchProductCategories(string $query): array
    {
        $searchTerm = '%'.$query.'%';
        $categories = ProductCategory::withCount('products')
            ->where(function ($q) use ($searchTerm) {
                $q->where('name->fa', 'like', $searchTerm)
                    ->orWhere('name->en', 'like', $searchTerm);
            })
            ->limit(10)
            ->get()
            ->map(fn (ProductCategory $category) => [
                'id' => $category->id,
                'title' => $category->trans('name'),
                'subtitle' => $category->products_count.' محصول',
                'image' => $category->image_url,
            ]);

        return $categories->toArray();
    }

    private function searchBlogs(string $query): array
    {
        $searchTerm = '%'.$query.'%';
        $blogs = Blog::active()
            ->with('category')
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm);
            })
            ->limit(10)
            ->get()
            ->map(fn (Blog $blog) => [
                'id' => $blog->id,
                'title' => $blog->title,
                'subtitle' => $blog->category->title ?? '',
                'image' => $blog->image_url,
            ]);

        return $blogs->toArray();
    }

    private function searchBlogCategories(string $query): array
    {
        $searchTerm = '%'.$query.'%';
        $categories = BlogCategory::active()
            ->withCount('blogs')
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm);
            })
            ->limit(10)
            ->get()
            ->map(fn (BlogCategory $category) => [
                'id' => $category->id,
                'title' => $category->title,
                'subtitle' => $category->blogs_count.' مقاله',
                'image' => $category->image_url,
            ]);

        return $categories->toArray();
    }
}
