<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\HeroSlideDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HeroSlideRequest;
use App\Models\HeroSlide;
use App\Services\HeroSlideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function __construct(protected HeroSlideService $service) {}

    public function index()
    {
        $slides = $this->service->getOrdered();

        return view('admin.hero-slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.hero-slides.create', ['formData' => $this->formData(null)]);
    }

    // ✅ نکته کلیدی: استفاده از HeroSlideRequest به جای Request معمولی
    public function store(HeroSlideRequest $request)
    {
        $dto = HeroSlideDTO::fromRequest($request);
        $this->service->store($dto);

        return redirect()->route('admin.hero-slides.index')->with('success', 'اسلاید با موفقیت ایجاد شد.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.edit', ['heroSlide' => $heroSlide, 'formData' => $this->formData($heroSlide)]);
    }

    /**
     * دادهٔ پیش‌فرض/ذخیره‌شدهٔ فرم (نوع لینک، وضعیت دکمه/امتیاز/بازدیدکننده، آیتم‌های اعتماد)
     * — منطق فرم از ویو خارج شده و این‌جا آماده می‌شود.
     *
     * @return array{linkType: string, linkValue: mixed, hasButton: bool, hasRating: bool, hasViewers: bool, trusts: array}
     */
    private function formData(?HeroSlide $slide): array
    {
        if ($slide) {
            return [
                'linkType' => $slide->linkType(),
                'linkValue' => $slide->linkable_id,
                'hasButton' => $slide->hasButton(),
                'hasRating' => $slide->hasRating(),
                'hasViewers' => $slide->hasViewers(),
                'trusts' => $slide->trustFormSeed(),
            ];
        }

        // اسلاید جدید — یک آیتم اعتماد پیش‌فرض «+۱۲,۰۰۰ مشتری راضی» از قبل اضافه می‌شود
        return [
            'linkType' => 'custom',
            'linkValue' => null,
            'hasButton' => false,
            'hasRating' => false,
            'hasViewers' => false,
            'trusts' => [
                ['icon' => '✓', 'fa' => '+۱۲,۰۰۰ مشتری راضی', 'en' => '+12,000 happy customers'],
            ],
        ];
    }

    public function update(HeroSlideRequest $request, HeroSlide $heroSlide)
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

    /**
     * ذخیرهٔ ترتیب درگ‌اندروپ (ایندکس) — همه اسلایدها با ترتیب جدید.
     */
    public function reorder(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:hero_slides,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        if (count($ids) !== HeroSlide::count()) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => __('admin.hero_slides.order_invalid')], 422)
                : back()->with('error', __('admin.hero_slides.order_invalid'));
        }

        foreach ($ids as $index => $id) {
            HeroSlide::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('admin.hero_slides.order_saved')]);
        }

        return back()->with('success', __('admin.hero_slides.order_saved'));
    }
}
