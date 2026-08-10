<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\BlogDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(protected BlogService $blogService) {}

    public function index(Request $request): View
    {
        $categories = BlogCategory::where('is_active', true)->get();
        $blogs = $this->blogService->getPaginatedBlogs($request->all());
        $blogs->appends($request->query()); // حفظ فیلترها در صفحه‌بندی

        return view('admin.blogs.index', compact('blogs', 'categories'));
    }

    public function create(): View
    {
        $categories = BlogCategory::where('is_active', true)->orderByDesc('id')->get();

        return view('admin.blogs.create', compact('categories'));
    }

    public function store(BlogRequest $request)
    {
        $blogDTO = BlogDTO::fromRequest($request, auth()->id());
        $this->blogService->store($blogDTO);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'مقاله با موفقیت ایجاد شد.');
    }

    public function edit(Blog $blog): View
    {
        $categories = BlogCategory::where('is_active', true)->orderByDesc('id')->get();

        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    public function update(BlogRequest $request, Blog $blog)
    {
        $blogDTO = BlogDTO::fromRequest($request, auth()->id());
        $this->blogService->update($blog, $blogDTO);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'مقاله با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Blog $blog)
    {
        $this->blogService->destroy($blog);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'مقاله با موفقیت حذف شد.');
    }
}
