<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Support\Facades\Storage;

class BlogCategoryController extends Controller
{
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
            $imagePath = $request->file('image')->store('blog-categories', 'public');
        }

        BlogCategory::create(array_merge($validated, ['image' => $imagePath]));

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
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
            if ($blogCategory->image) {
                Storage::disk('public')->delete($blogCategory->image);
            }
            $imagePath = $request->file('image')->store('blog-categories', 'public');
        }

        $blogCategory->update(array_merge($validated, ['image' => $imagePath]));

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'دسته‌بندی با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(BlogCategory $blogCategory)
    {
        if ($blogCategory->blogs()->count() > 0) {
            return redirect()->route('admin.blog-categories.index')
                ->with('error', 'نمی‌توان این دسته‌بندی را حذف کرد چون دارای مقاله است.');
        }

        if ($blogCategory->image) {
            Storage::disk('public')->delete($blogCategory->image);
        }

        $blogCategory->delete();

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }
}
