<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\HeroSlideDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HeroSlideRequest;
use App\Models\HeroSlide;
use App\Services\HeroSlideService;
use Illuminate\Http\RedirectResponse;

/**
 * هیرو صفحهٔ اصلی — نسخهٔ مینیمال: سایت تنها یک هیرو نمایش می‌دهد،
 * پس این صفحه هم یک ادیتور تک‌رکورد است (بدون لیست اسلاید و اسلایدر).
 */
class HeroSlideController extends Controller
{
    public function __construct(protected HeroSlideService $service) {}

    public function index()
    {
        $heroSlide = $this->service->primaryForAdmin();

        return view('admin.hero-slides.index', [
            'heroSlide' => $heroSlide,
            'formData' => $this->formData($heroSlide),
            'extraCount' => $this->service->extraCount($heroSlide),
        ]);
    }

    public function update(HeroSlideRequest $request): RedirectResponse
    {
        $dto = HeroSlideDTO::fromRequest($request);
        $primary = $this->service->primaryForAdmin();
        $this->service->save($primary, $dto);

        // پاک‌سازی اسلایدهای قدیمی/اضافی — فقط وقتی کاربر در فرم درخواست کرده باشد
        if ($request->boolean('prune_extras')) {
            $this->service->pruneOthers($primary);
        }

        return redirect()->route('admin.hero-slides.index')->with('success', __('admin.hero_slides.updated'));
    }

    /**
     * دادهٔ پیش‌فرض/ذخیره‌شدهٔ فرم (نوع لینک، وضعیت دکمه)
     * — منطق فرم از ویو خارج شده و این‌جا آماده می‌شود.
     *
     * @return array{linkType: string, linkValue: mixed, hasButton: bool, selectedModel: array|null}
     */
    private function formData(?HeroSlide $slide): array
    {
        if ($slide) {
            return [
                'linkType' => $slide->linkType(),
                'linkValue' => $slide->linkable_id,
                'hasButton' => $slide->hasButton(),
                // مدل لینک‌شده به فرمت نتایج جستجو — برای auto-select در فرم
                'selectedModel' => $slide->linkableFormSeed(),
            ];
        }

        return [
            'linkType' => 'custom',
            'linkValue' => null,
            'hasButton' => false,
            'selectedModel' => null,
        ];
    }
}
