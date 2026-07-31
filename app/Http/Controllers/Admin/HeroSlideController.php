<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Services\HeroSlideService;
use App\DTOs\HeroSlideDTO;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function __construct(protected HeroSlideService $service) {}

    public function index()
    {
        $slides = $this->service->getPaginated();
        return view('admin.hero-slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.hero-slides.create');
    }

    public function store(Request $request)
    {
        $dto = HeroSlideDTO::fromRequest($request);
        $this->service->store($dto);
        return redirect()->route('admin.hero-slides.index')->with('success', 'اسلاید با موفقیت ایجاد شد.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.edit', compact('heroSlide'));
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $dto = HeroSlideDTO::fromRequest($request);
        $this->service->update($heroSlide, $dto);
        return redirect()->route('admin.hero-slides.index')->with('success', 'اسلاید با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $this->service->destroy($heroSlide);
        return redirect()->route('admin.hero-slides.index')->with('success', 'اسلاید با موفقیت حذف شد.');
    }
}
