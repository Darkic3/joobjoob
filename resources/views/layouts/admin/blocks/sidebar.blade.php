@php
    $user = auth()->user();
@endphp

<div class="d-lg-block d-none">
    <div class="sidebar px-4 py-4 py-md-4 shadow m-0 rounded-0" id="sidebar">
        <div class="content d-flex flex-column h-100">

            <div class="xs d-flex align-items-center justify-content-between mb-4">
                <a href="/" target="_blank" class="mb-0 d-flex align-items-center justify-content-start text-decoration-none">
                    <span class="h6 fw-bold ms-2 mb-0 title" style="color:#5d596c;">پخش اکسیر</span>
                </a>
                <i class="h6 bi bi-record-circle m-0" type="button" id="openMenu" style="color:#5d596c;"></i>
            </div>

            <ul class="menu-list flex-grow-1">

                {{-- بخش اصلی --}}
                <li class="mb-2">
                    <a class="m-link {{ request()->routeIs('admin.dashboard.*') ? 'active' : '' }}" href="{{route('admin.dashboard.index')}}">
                        <i class="bi bi-grid-fill fs-5 me-2 d-flex"></i>
                        <span class="title">داشبورد</span>
                    </a>
                </li>

                {{-- جداکننده: ارتباطات (اولویت بالا) --}}
                <li class="menu-header small text-muted text-uppercase mt-4 mb-2" style="font-size: 0.7rem; opacity: 0.6;">تعاملات</li>

                @if($user->hasAccess('admin.recruitment.index'))
                    <li>
                        <a class="m-link {{ request()->routeIs('admin.recruitment.*') ? 'active' : '' }}" href="{{route('admin.recruitment.index')}}">
                            <i class="bi bi-person-plus-fill fs-5 me-2 d-flex text-primary"></i>
                            <span class="title">درخواست‌های استخدام</span>
                            @if(isset($unreadRecruitmentCount) && $unreadRecruitmentCount > 0)
                                <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 10px;">{{ $unreadRecruitmentCount }}</span>
                            @endif
                        </a>
                    </li>
                @endif

                @if($user->hasAccess('admin.contact.index'))
                    <li>
                        <a class="m-link {{ request()->routeIs('admin.contact.*') ? 'active' : '' }}" href="{{route('admin.contact.index')}}">
                            <i class="bi bi-envelope-paper-fill fs-5 me-2 d-flex text-danger"></i>
                            <span class="title">درخواست مخاطبین</span>
                            @if(isset($unreadContactCount) && $unreadContactCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 10px;">{{ $unreadContactCount }}</span>
                            @endif
                        </a>
                    </li>
                @endif

                @if($user->hasAccess('admin.comments.index'))
                    <li>
                        <a class="m-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{route('admin.comments.index')}}">
                            <i class="bi bi-chat-dots-fill fs-5 me-2 d-flex text-warning"></i>
                            <span class="title">نظرات کاربران</span>
                            @if(isset($unreadCommentCount) && $unreadCommentCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill ms-auto" style="font-size: 10px;">{{ $unreadCommentCount }}</span>
                            @endif
                        </a>
                    </li>
                @endif

                {{-- جداکننده: محتوا --}}
                <li class="menu-header small text-muted text-uppercase mt-4 mb-2" style="font-size: 0.7rem; opacity: 0.6;">مدیریت محتوا</li>

                @if($user->hasAccess('admin.blogs.index'))
                    <li class="nav-item">
                        <a href="#submenu-blog" class="nav-link m-link" data-bs-toggle="collapse" role="button">
                            <i class="bi bi-file-earmark-post fs-5 me-2 d-flex"></i>
                            <span class="title">وبلاگ و مقالات</span>
                            <span class="ms-auto"><i class="bi bi-chevron-down small"></i></span>
                        </a>
                        <ul class="collapse sub-menu ps-3" id="submenu-blog">
                            <li><a class="ms-link" href="{{route('admin.blogs.index')}}">لیست مقالات</a></li>
                            @if($user->hasAccess('admin.blog-category.index'))
                                <li><a class="ms-link" href="{{route('admin.blog-category.index')}}">دسته‌بندی مقالات</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($user->hasAccess('admin.news.index'))
                    <li class="nav-item">
                        <a href="#submenu-news" class="nav-link m-link" data-bs-toggle="collapse" role="button">
                            <i class="bi bi-newspaper fs-5 me-2 d-flex"></i>
                            <span class="title">اخبار</span>
                            <span class="ms-auto"><i class="bi bi-chevron-down small"></i></span>
                        </a>
                        <ul class="collapse sub-menu ps-3" id="submenu-news">
                            <li><a class="ms-link" href="{{route('admin.news.index')}}">لیست اخبار</a></li>
                            @if($user->hasAccess('admin.news-category.index'))
                                <li><a class="ms-link" href="{{route('admin.news-category.index')}}">دسته‌بندی اخبار</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($user->hasAccess('admin.advertisement.index'))
                    <li class="nav-item">
                        <a href="#submenu-adv" class="nav-link m-link" data-bs-toggle="collapse" role="button">
                            <i class="bi bi-megaphone fs-5 me-2 d-flex"></i>
                            <span class="title">آگهی‌ها</span>
                            <span class="ms-auto"><i class="bi bi-chevron-down small"></i></span>
                        </a>
                        <ul class="collapse sub-menu ps-3" id="submenu-adv">
                            <li><a class="ms-link" href="{{route('admin.advertisement.index')}}">لیست آگهی‌ها</a></li>
                            @if($user->hasAccess('admin.advertisement-category.index'))
                                <li><a class="ms-link" href="{{route('admin.advertisement-category.index')}}">دسته‌بندی آگهی</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($user->hasAccess('admin.sliders.index'))
                    <li><a class="m-link" href="{{route('admin.sliders.index')}}"><i class="bi bi-images fs-5 me-2 d-flex"></i><span class="title">اسلایدرها</span></a></li>
                @endif

                {{-- جداکننده: اطلاعات سازمانی --}}
                <li class="menu-header small text-muted text-uppercase mt-4 mb-2" style="font-size: 0.7rem; opacity: 0.6;">ساختار سازمانی</li>

                @if($user->hasAccess('admin.information.index'))
                    <li><a class="m-link" href="{{route('admin.information.index')}}"><i class="bi bi-info-square fs-5 me-2 d-flex"></i><span class="title">درباره ما</span></a></li>
                @endif

                @if($user->hasAccess('admin.branches.index'))
                    <li><a class="m-link" href="{{route('admin.branches.index')}}"><i class="bi bi-geo-alt fs-5 me-2 d-flex"></i><span class="title">شعب</span></a></li>
                @endif

                @if($user->hasAccess('admin.suppliers.index'))
                    <li><a class="m-link" href="{{route('admin.suppliers.index')}}"><i class="bi bi-truck fs-5 me-2 d-flex"></i><span class="title">تامین‌کنندگان</span></a></li>
                @endif

                @if($user->hasAccess('admin.managers.index') || $user->hasAccess('admin.teams.index'))
                    <li class="nav-item">
                        <a href="#submenu-team" class="nav-link m-link" data-bs-toggle="collapse" role="button">
                            <i class="bi bi-people-fill fs-5 me-2 d-flex"></i>
                            <span class="title">مدیران</span>
                            <span class="ms-auto"><i class="bi bi-chevron-down small"></i></span>
                        </a>
                        <ul class="collapse sub-menu ps-3" id="submenu-team">
                            @if($user->hasAccess('admin.managers.index'))
                                <li><a class="ms-link" href="{{route('admin.managers.index')}}">مدیران ستاد</a></li>
                            @endif
                            @if($user->hasAccess('admin.teams.index'))
                                <li><a class="ms-link" href="{{route('admin.teams.index')}}">هیئت مدیره</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($user->hasAccess('admin.contact_infos.index'))
                    <li><a class="m-link" href="{{route('admin.contact_infos.index')}}"><i class="bi bi-telephone-fill fs-5 me-2 d-flex"></i><span class="title">اطلاعات تماس</span></a></li>
                @endif

                <li class="menu-header small text-muted text-uppercase mt-4 mb-2" style="font-size: 0.7rem; opacity: 0.6;">تنظیمات سیستم</li>

                @if($user->hasAccess('admin.user.index') || $user->hasAccess('admin.permissions.index'))
                    <li class="nav-item">
                        <a href="#submenu-auth" class="nav-link m-link" data-bs-toggle="collapse" role="button">
                            <i class="bi bi-shield-lock-fill fs-5 me-2 d-flex"></i>
                            <span class="title">مدیریت دسترسی</span>
                            <span class="ms-auto"><i class="bi bi-chevron-down small"></i></span>
                        </a>
                        <ul class="collapse sub-menu ps-3" id="submenu-auth">
                            @if($user->hasAccess('admin.user.index'))
                                <li><a class="ms-link" href="{{ route('admin.user.index') }}">ادمین‌ها</a></li>
                            @endif
                            @if($user->hasAccess('admin.permissions.index'))
                                <li><a class="ms-link" href="{{ route('admin.permissions.index') }}">نقش‌ها و دسترسی‌ها</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($user->hasAccess('admin.seo.index'))
                    <li><a class="m-link" href="{{route('admin.seo.index')}}"><i class="bi bi-search fs-5 me-2 d-flex"></i><span class="title">سئو</span></a></li>
                @endif

                @if($user->hasAccess('admin.logos.index'))
                    <li><a class="m-link" href="{{route('admin.logos.index')}}"><i class="bi bi-award fs-5 me-2 d-flex"></i><span class="title">نمادهای فوتر</span></a></li>
                @endif

                @if($user->hasAccess('admin.menu.index'))
                    <li><a class="m-link" href="{{route('admin.menu.index')}}"><i class="bi bi-list-stars fs-5 me-2 d-flex text-info"></i><span class="title">تنظیمات منو</span></a></li>
                @endif

                @if($user->hasAccess('admin.settings.edit'))
                    <li><a class="m-link" href="{{route('admin.settings.edit')}}"><i class="bi bi-gear-fill fs-5 me-2 d-flex"></i><span class="title">تنظیمات کلی</span></a></li>
                @endif

                @if($user->hasAccess('admin.request-logs.index'))
                    <li><a class="m-link" href="{{route('admin.request-logs.index')}}"><i class="bi bi-activity fs-5 me-2 d-flex text-secondary"></i><span class="title">لاگ‌های سیستم</span></a></li>
                @endif

                <li class="mt-4 mb-5">
                    <a class="m-link text-danger border border-danger border-opacity-10 rounded" href="{{ route('admin.logout') }}">
                        <i class="bi bi-power me-2 d-flex fs-5"></i>
                        <span class="title">خروج از حساب</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
