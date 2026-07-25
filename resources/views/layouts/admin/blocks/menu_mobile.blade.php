<div
    class="sidebar header bg-white px-0 py-3 border mt-lg-4 mt-md-3 mt-sm-2 mt-1 rounded-custom shadow d-lg-none d-block">
    @include('layouts.admin.blocks.header_mobile')
    <div class="offcanvas offcanvas-end w-75" data-bs-scroll="true" tabindex="-1" id="offcanvasderawer"
         aria-labelledby="offcanvasderawerLabel">
        <div class="offcanvas-header d-flex align-items-center justify-content-between px-3 pb-0">
            <div class="xs p-0">
                <a href="/" target="_blank"
                   class="mb-0 d-flex align-items-center justify-content-start me-2 p-0">
                    <img src="{{asset('assets/admin/images/fav.png')}}" width="50px" height="50px"/>
                    <span class="h6 fw-bold ms-2 mb-0" style="color:#5d596c;">
                        سمیع زاده
                    </span>
                </a>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body px-0">
            <div class="d-flex flex-column h-100">
                <ul class="menu-list flex-grow-1 mt-3 px-3 list-untyled">
                    <li>
                        <a class="m-link {{ request()->routeIs('admin.dashboard.index') ? 'active' : '' }}"
                           href="{{route('admin.dashboard.index')}}">
                            <i class="bi bi-speedometer2 fs-5 me-2 d-flex"></i>
                            <span class="title">داشبورد</span>
                        </a>
                    </li>
                        <li>
                            <a class="m-link {{ request()->routeIs('admin.roles.list') ? 'active' : '' }}"
                               href="">
                                <i class="bi bi-person-check me-2 d-flex fs-5"></i>
                                <span class="title">سطح دسترسی</span>
                            </a>
                        </li>
                        <li>
                            <a class="m-link {{ request()->routeIs('admin.admin.list') ? 'active' : '' }}"
                               href="">
                                <i class="bi bi-people-fill me-2 d-flex fs-5"></i>
                                <span class="title">ادمین ها</span>
                            </a>
                        </li>
                        <li>
                            <a class="m-link {{ request()->routeIs('admin.languages.list') ? 'active' : '' }}"
                               href="">
                                <i class="bi bi-translate me-2 d-flex fs-5"></i>
                                <span class="title">زبانها</span>
                            </a>
                        </li>
                        <li>
                            <a class="m-link {{ request()->routeIs('admin.services.list') ? 'active' : '' }}"
                               href="">
                                <i class="bi bi-box me-2 d-flex fs-5"></i>
                                <span class="title">خدمات</span>
                            </a>
                        </li>
                        <li>
                            <a class="m-link {{ request()->routeIs('admin.achievements.list') ? 'active' : '' }}"
                               href="">
                                <i class="bi bi-trophy me-2 d-flex fs-5"></i>
                                <span class="title">سوابق اجرایی</span>
                            </a>
                        </li>
{{--                    @if (auth()->user()->hasPermission('admin.activities.list'))--}}
                        <li>
                            <a class="m-link {{ request()->routeIs('admin.activities.list') ? 'active' : '' }}"
                               href="">
                                <i class="bi bi-activity me-2 d-flex fs-5"></i>
                                <span class="title">سوابق آموزشی</span>
                            </a>
                        </li>
{{--                    @endif--}}
                        <li>
                            <a class="m-link {{ request()->routeIs('admin.experiences.list') ? 'active' : '' }}"
                               href="">
                                <i class="bi bi-clock-history me-2 d-flex fs-5"></i>
                                <span class="title"> سوابق علمی</span>
                            </a>
                        </li>

                        <li>
                            <a class="m-link {{ request()->routeIs('admin.share.list') ? 'active' : '' }}"
                               href="">
                                <i class=" bi bi-share me-2 d-flex fs-5"></i>
                                <span class="title">اشتراک ها</span>
                            </a>
                        </li>

                        <li>
                            <a class="m-link {{ request()->routeIs('admin.share_categories.list') ? 'active' : '' }}"
                               href="">
                                <i class="bi bi-share-fill me-2 d-flex fs-5"></i>
                                <span class="title">دسته بندی اشتراک ها</span>
                            </a>
                        </li>
                    <li>
                        <a class="m-link {{ request()->routeIs('admin.share_categories.list') ? 'active' : '' }}"
                           href="">
                            <i class="bi bi-power me-2 d-flex fs-5"></i>
                            <span class="title">خروج</span>
                        </a>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</div>
