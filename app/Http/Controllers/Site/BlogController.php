<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with('category')
            ->active()
            ->latest()
            ->paginate(9);

        $categories = BlogCategory::active()->withCount('blogs')->get();

        // SEO: فهرست بلاگ مدل ندارد ⇒ متای مسیر
        seo()
            ->forPath('/blogs')
            ->listItems(
                $blogs->getCollection()
                    ->map(fn (Blog $blog) => [
                        'name' => (string) $blog->title,
                        'url' => localizedRoute('blogs.show', ['blog' => $blog->slug]),
                    ])
                    ->all(),
                ($blogs->currentPage() - 1) * $blogs->perPage(),
            );

        return view('site.blogs.index', compact('blogs', 'categories'));
    }

    public function show(Request $request)
    {
        $blog = Blog::where('slug', $request->route('blog'))->firstOrFail();

        abort_unless($blog->is_active, 404);

        // author هم لود می‌شود تا اسکیمای BlogPosting نام نویسندهٔ واقعی را داشته باشد
        // (سیگنال E-E-A-T) و به نام سایت تنزل نکند.
        $blog->load(['category', 'author']);
        $blog->incrementViews();

        // مقالات مرتبط: از همان دسته‌بندی (اگر دسته‌ای داشته باشد)
        $related = Blog::active()
            ->where('id', '!=', $blog->id)
            ->when($blog->blog_category_id, fn ($q) => $q->where('blog_category_id', $blog->blog_category_id))
            ->latest()
            ->take(3)
            ->get();

        // SEO: متای اختصاصی مقاله (per-locale) + عنوان/خلاصه/تصویر مقاله
        seo()->forModel($blog);

        return view('site.blogs.show', compact('blog', 'related'));
    }

    public function category(Request $request)
    {
        $category = BlogCategory::where('slug', $request->route('category'))->firstOrFail();

        abort_unless($category->is_active, 404);

        // مقالات این دسته + تمام زیردسته‌ها (در هر عمقی)
        $ids = $this->categoryIdsWithDescendants([$category->id]);
        $blogs = Blog::with('category')
            ->active()
            ->whereIn('blog_category_id', $ids)
            ->latest()
            ->paginate(9);

        $categories = BlogCategory::active()->withCount('blogs')->get();
        $activeCategory = $category;

        // SEO: متای اختصاصی دسته‌بندی مقاله
        seo()
            ->forModel($category)
            ->listItems(
                $blogs->getCollection()
                    ->map(fn (Blog $blog) => [
                        'name' => (string) $blog->title,
                        'url' => localizedRoute('blogs.show', ['blog' => $blog->slug]),
                    ])
                    ->all(),
                ($blogs->currentPage() - 1) * $blogs->perPage(),
            );

        return view('site.blogs.index', compact('blogs', 'categories', 'activeCategory'));
    }

    /**
     * شناسه‌های دسته‌بندی + شناسه‌های تمام زیرشاخه‌ها (در هر عمقی).
     */
    private function categoryIdsWithDescendants(array $ids): array
    {
        $all = $ids;
        $found = $ids;

        while ($found) {
            $found = BlogCategory::whereIn('parent_id', $found)->pluck('id')->all();
            $all = array_merge($all, $found);
        }

        return array_values(array_unique($all));
    }
}
