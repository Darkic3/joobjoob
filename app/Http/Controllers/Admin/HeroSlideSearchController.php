<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $searchTerm = '%' . $query . '%';
        $products = Product::where('status', 'published')
            ->where(function ($q) use ($searchTerm) {
                $q->where('name->fa', 'like', $searchTerm)
                    ->orWhere('name->en', 'like', $searchTerm);
            })
            ->with('images')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $image = $product->images->first();
                return [
                    'id' => $product->id,
                    'title' => $product->trans('name'),
                    'subtitle' => number_format($product->price) . ' تومان',
                    'image' => $image ? asset(config('paths.upload', 'uploads') . '/products/' . $image->image) : null,
                ];
            });

        return $products->toArray();
    }

    private function searchProductCategories(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        $categories = ProductCategory::withCount('products')
            ->where('name->fa', 'like', $searchTerm)
            ->orWhere('name->en', 'like', $searchTerm)
            ->limit(10)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->trans('name'),
                    'subtitle' => $category->products_count . ' محصول',
                    'image' => $category->image ? asset(config('paths.upload', 'uploads') . '/categories/' . $category->image) : null,
                ];
            });

        return $categories->toArray();
    }

    private function searchBlogs(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        $blogs = Blog::where('is_active', true)
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm);
            })
            ->limit(10)
            ->get()
            ->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'subtitle' => $blog->category->title ?? '',
                    'image' => $blog->image ? asset(config('paths.upload', 'uploads') . '/blogs/' . $blog->image) : null,
                ];
            });

        return $blogs->toArray();
    }

    private function searchBlogCategories(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        $categories = BlogCategory::where('is_active', true)
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm);
            })
            ->limit(10)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'subtitle' => $category->blogs_count . ' مقاله',
                    'image' => $category->image ? asset(config('paths.upload', 'uploads') . '/blog-categories/' . $category->image) : null,
                ];
            });

        return $categories->toArray();
    }
}
