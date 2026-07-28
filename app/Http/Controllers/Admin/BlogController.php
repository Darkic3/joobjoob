<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        $query = Blog::with('category');

        // فیلتر جستجو
        if (request('search')) {
            $query->where('title', 'like', '%' . request('search') . '%');
        }

        // فیلتر دسته‌بندی
        if (request('category')) {
            $query->where('blog_category_id', request('category'));
        }

        // فیلتر وضعیت
        if (request('status') !== null) {
            $query->where('is_active', request('status'));
        }

        $blogs = $query->latest()->paginate(15);
        $categories = BlogCategory::where('is_active', true)->get();

        return view('admin.blogs.index', compact('blogs', 'categories'));
    }

    public function create()
    {
        $categories = BlogCategory::where('is_active', true)->get();
        return view('admin.blogs.create', compact('categories'));
    }

    public function store(BlogRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blogs', 'public');
        }

        Blog::create(array_merge($validated, [
            'image' => $imagePath,
            'user_id' => auth()->id(),
        ]));

        return redirect()->route('admin.blogs.index')
            ->with('success', 'مقاله با موفقیت ایجاد شد.');
    }

    public function edit(Blog $blog)
    {
        $categories = BlogCategory::where('is_active', true)->get();
        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    public function update(BlogRequest $request, Blog $blog)
    {
        $validated = $request->validated();

        $imagePath = $blog->image;
        if ($request->hasFile('image')) {
            if ($blog->image) {
                Storage::disk('public')->delete($blog->image);
            }
            $imagePath = $request->file('image')->store('blogs', 'public');
        }

        $blog->update(array_merge($validated, ['image' => $imagePath]));

        return redirect()->route('admin.blogs.index')
            ->with('success', 'مقاله با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Blog $blog)
    {
        if ($blog->image) {
            Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'مقاله با موفقیت حذف شد.');
    }
}
