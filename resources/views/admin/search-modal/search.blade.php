@props([
    'route' => null,
    'hasTitle' => true,
    'hasCategory' => false,
    'categories' => [],
    'categoryRef' => 'category_url',
    'hasUrl' => false,
    'hasStatus' => false,
    'hasShowInFirstPage' => false,
    'hasReadStatus' => false,
    'hasDepartment' => false,
    'email' => false,
    'hasDate' => false,
])

@once
    @push('styles')
        <style>
            jdp-container { z-index: 100000 !important; }
            jdp-overlay { z-index: 100000 !important; }
            .search-modal-content { border: none; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1); }
            .modal-header-custom { background-color: #f8f9fa; border-bottom: 1px solid #edf2f7; }
            .form-label-custom { font-size: 0.9rem; font-weight: 600; color: #4a5568; margin-bottom: 0.5rem; }
            .custom-input-group { background-color: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem; transition: all 0.2s ease-in-out; display: flex; align-items: center; padding: 0.1rem 0.5rem; }
            .custom-input-group:focus-within { border-color: #696cff; box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.15); }
            .custom-input-group .input-group-text { background: transparent; border: none; color: #a1acb8; }
            .custom-input-group .form-control, .custom-input-group .form-select { border: none; box-shadow: none; background: transparent; padding: 0.6rem 0.5rem; font-size: 0.95rem; color: #566a7f; }
            .custom-input-group .form-control::placeholder { color: #b4bdc6; font-size: 0.85rem; }
        </style>
    @endpush
@endonce

<button type="button" class="btn btn-secondary btn-custom rounded-custom d-flex align-items-center px-3 mx-1"
        data-bs-toggle="modal" data-bs-target="#searchModal" title="جستجو پیشرفته">
    <i class="bi bi-search"></i>
    <span class="ms-2 d-none d-md-inline">جستجو</span>

    @if(request()->anyFilled(['title', 'url', 'is_active', 'start_date', 'end_date', $categoryRef, 'department', 'email', 'is_read']))
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
            <span class="visually-hidden">فیلتر فعال</span>
        </span>
    @endif
</button>

@push('modals')
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content search-modal-content rounded-4">

                <div class="modal-header modal-header-custom px-4 py-3 rounded-top-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white p-2 rounded-circle shadow-sm me-3 d-flex align-items-center justify-content-center text-primary">
                            <i class="bi bi-sliders2 fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="searchModalLabel">فیلتر پیشرفته</h5>
                            <small class="text-muted">نتایج را بر اساس پارامترهای زیر محدود کنید</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <form action="{{ $route ?? url()->current() }}" method="GET" id="searchForm">
                        <div class="row g-4">

                            @if($hasTitle)
                                <div class="col-md-6">
                                    <label class="form-label-custom">عنوان</label>
                                    <div class="custom-input-group">
                                        <span class="input-group-text"><i class="bi bi-person fs-5"></i></span>
                                        <input type="text" name="name" class="form-control"
                                               value="{{ request('name') ?? request('title') }}" placeholder="جستجو عنوان...">
                                    </div>
                                </div>
                            @endif

                            @if($email)
                                <div class="col-md-6">
                                    <label class="form-label-custom">ایمیل</label>
                                    <div class="custom-input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope fs-5"></i></span>
                                        <input type="text" name="email" class="form-control"
                                               value="{{ request('email') }}" placeholder="جستجو در ایمیل..." dir="ltr">
                                    </div>
                                </div>
                            @endif

                            @if($hasDepartment)
                                <div class="col-md-6">
                                    <label class="form-label-custom">دپارتمان</label>
                                    <div class="custom-input-group">
                                        <span class="input-group-text"><i class="bi bi-building fs-5"></i></span>
                                        <select name="department" class="form-select cursor-pointer">
                                            <option value="">همه دپارتمان‌ها</option>
                                            <option value="management" {{ request('department') == 'management' ? 'selected' : '' }}>مدیریت</option>
                                            <option value="support" {{ request('department') == 'support' ? 'selected' : '' }}>پشتیبانی</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if($hasUrl)
                                <div class="col-md-6">
                                    <label class="form-label-custom">آدرس (URL)</label>
                                    <div class="custom-input-group">
                                        <span class="input-group-text"><i class="bi bi-link-45deg fs-4"></i></span>
                                        <input type="text" name="url" class="form-control"
                                               style="direction: ltr; text-align: left"
                                               value="{{ request('url') }}" placeholder="example-url-slug">
                                    </div>
                                </div>
                            @endif

                            @if($hasCategory)
                                <div class="col-md-12">
                                    <label class="form-label-custom">دسته‌بندی</label>
                                    <div class="custom-input-group">
                                        <span class="input-group-text"><i class="bi bi-folder2-open fs-5"></i></span>
                                        <select name="{{ $categoryRef }}" class="form-select cursor-pointer">
                                            <option value="">همه دسته‌ها</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->url }}" {{ request($categoryRef) == $cat->url ? 'selected' : '' }}>
                                                    {{ $cat->title ?? $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <div class="p-3 rounded-3" style="background-color: #f8f9fa;">
                                    <div class="row g-3">
                                        @if($hasStatus)
                                            <div class="col-md-6">
                                                <label class="form-label-custom mb-1">وضعیت انتشار</label>
                                                <select name="is_active" class="form-select border shadow-sm">
                                                    <option value="">نمایش همه</option>
                                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>🟢 فعال</option>
                                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>🔴 غیرفعال</option>
                                                </select>
                                            </div>
                                        @endif

                                        @if($hasReadStatus)
                                            <div class="col-md-6">
                                                <label class="form-label-custom mb-1">وضعیت پیام</label>
                                                <select name="is_read" class="form-select border shadow-sm">
                                                    <option value="">همه پیام‌ها</option>
                                                    <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>🔴 جدید (خوانده نشده)</option>
                                                    <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>🟢 خوانده شده</option>
                                                </select>
                                            </div>
                                        @endif

                                        @if($hasShowInFirstPage)
                                            <div class="col-md-6">
                                                <label class="form-label-custom mb-1">نمایش در صفحه اول</label>
                                                <select name="show_in_first_page" class="form-select border shadow-sm">
                                                    <option value="">مهم نیست</option>
                                                    <option value="1" {{ request('show_in_first_page') === '1' ? 'selected' : '' }}>بله</option>
                                                    <option value="0" {{ request('show_in_first_page') === '0' ? 'selected' : '' }}>خیر</option>
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($hasDate)
                                <div class="col-12">
                                    <label class="form-label-custom">بازه زمانی</label>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="custom-input-group">
                                                <span class="input-group-text text-muted small">از</span>
                                                <input type="text" name="start_date" class="form-control text-center"
                                                       value="{{ request('start_date') }}" placeholder="1402/01/01" data-jdp autocomplete="off">
                                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-input-group">
                                                <span class="input-group-text text-muted small">تا</span>
                                                <input type="text" name="end_date" class="form-control text-center"
                                                       value="{{ request('end_date') }}" placeholder="1402/12/29" data-jdp autocomplete="off">
                                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </form>
                </div>

                <div class="modal-footer border-top-0 pt-0 pb-4 justify-content-between px-4">
                    @if(request()->anyFilled(['title', 'name', 'url', 'is_active', 'start_date', 'end_date', $categoryRef, 'department', 'email', 'is_read']))
                        <a href="{{ $route ?? url()->current() }}" class="btn btn-light text-danger border hover-shadow btn-sm px-3 rounded-pill transition-all">
                            <i class="bi bi-x-circle me-1"></i> پاک کردن فیلترها
                        </a>
                    @else
                        <div></div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border px-4 rounded-pill" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" form="searchForm" class="btn btn-primary px-5 rounded-pill shadow-sm">
                            <i class="bi bi-search me-2"></i> جستجو
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush
