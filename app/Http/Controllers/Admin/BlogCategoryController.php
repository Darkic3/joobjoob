<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Models\BlogCategory;

class BlogCategoryController extends Controller
{
    protected const UPLOAD_FOLDER = 'blog-categories';

    public function index()
    {
        $categories = BlogCategory::withCount('blogs')
            ->orderBy('sort_order')
            ->get();

        return view('admin.blog-categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = BlogCategory::where('is_active', true)->get();

        return view('admin.blog-categories.create', compact('parentCategories'));
    }

    public function store(BlogCategoryRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = ImageHandler::handle(self::UPLOAD_FOLDER, $request->file('image'));
        }

        BlogCategory::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $validated['url'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_active' => $validated['is_active'] ?? true,
            'show_in_first_page' => $validated['show_in_first_page'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', __('admin.blog_categories.created'));
    }

    public function edit(BlogCategory $blogCategory)
    {
        $parentCategories = BlogCategory::where('is_active', true)
            ->where('id', '!=', $blogCategory->id)
            ->get();

        return view('admin.blog-categories.edit', compact('blogCategory', 'parentCategories'));
    }

    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory)
    {
        $validated = $request->validated();

        $imagePath = $blogCategory->image;
        if ($request->hasFile('image')) {
            // اول فایل جدید آپلود شود — اگر ناموفق بود تصویر قبلی پاک نمی‌شود
            $newPath = ImageHandler::handle(self::UPLOAD_FOLDER, $request->file('image'));
            if ($newPath) {
                if ($blogCategory->image) {
                    ImageHandler::delete(self::UPLOAD_FOLDER, $blogCategory->image);
                }
                $imagePath = $newPath;
            }
        }

        $blogCategory->update([
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $validated['url'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_active' => $validated['is_active'] ?? true,
            'show_in_first_page' => $validated['show_in_first_page'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', __('admin.blog_categories.updated'));
    }

    public function destroy(BlogCategory $blogCategory)
    {
        if ($blogCategory->blogs()->count() > 0) {
            return redirect()->route('admin.blog-categories.index')
                ->with('error', __('admin.blog_categories.has_blogs_error'));
        }

        if ($blogCategory->image) {
            ImageHandler::delete(self::UPLOAD_FOLDER, $blogCategory->image);
        }

        $blogCategory->delete();

        return redirect()->route('admin.blog-categories.index')
            ->with('success', __('admin.blog_categories.deleted'));
    }
}
