<div class="navbar menu-desktop navbar-expand-lg position-sticky top-0 bg-white-custom-2 z-99 d-none d-lg-block">
    <div class="container">
        <a class="navbar-brand me-0 pe-xl-3" href="{{ route('site.home-page.index') }}">
            <img src="{{ $setting->imageFile('logo') }}" width="145" alt="exir logo">
        </a>

        <div class="collapse navbar-collapse menu" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {{-- صفحه اصلی --}}
                <li class="nav-item">
                    <a class="nav-link @yield('menu-home') fs-costum5-sm pe-xl-3 w-max fw-bolder"
                       href="{{ route('site.home-page.index') }}">
                        <img src="{{ asset('assets/site/images/home.svg') }}" alt="صفحه اصلی">
                        صفحه اصلی
                    </a>
                </li>

                @foreach($menu_items_show as $menu)
                    @php
                        $yieldName = match($menu->name) {
                            'آگهی ها' => 'menu-ad',
                            'اخبار و مقالات' => 'menu-news',
                            'مدیران' => 'menu-team',
                            'درباره ما' => 'menu-about',
                            'تماس با ما' => 'menu-contact',
                            default => ''
                        };

                        $isAds = ($menu->type == 'dyno' && $menu->name == 'آگهی ها');
                        $isServices = ($menu->name == 'خدمات آنلاین'); // متغیر اصلاح شده
                        $hasChildren = ($menu->children->count() > 0 || ($isAds && isset($ads_menu_cat)));
                        $showDropdown = ($menu->drop && $hasChildren);
                    @endphp

                    <li class="nav-item {{ $showDropdown ? 'dropdown position-relative custom-hover' : '' }}">
                        <a class="nav-link @if($yieldName) @yield($yieldName) @endif {{ ($menu->route_name && request()->routeIs($menu->route_name)) ? 'active' : '' }} fs-costum5-sm pe-xl-3 w-max fw-bolder"
                           href="{{ $showDropdown ? 'javascript:void(0)' : ($menu->route_name ? route($menu->route_name) : ($menu->url ?? '#')) }}"
                           @if($menu->new_tab) target="_blank" rel="noopener noreferrer" @endif>
                            {{ $menu->name }}
                            @if($showDropdown)
                                <img src="{{ asset('assets/site/images/drop-down.svg') }}" alt="dropdown">
                            @endif
                        </a>

                        @if($showDropdown)
                            <ul class="dropdown-menu custom-menu px-2 {{ !$isServices ? 'bg-white-custom' : '' }}">
                                {{-- حالت اول: آگهی‌های داینامیک --}}
                                @if($isAds)
                                    @foreach ($ads_menu_cat as $cat)
                                        <li>
                                            <a class="dropdown-item fs-costum5-sm p-0 d-flex align-items-center"
                                               href="{{ route('site.ads.list', $cat->url) }}">
                                                <i class="bi bi-dot"></i> {{ Str::limit($cat->title, 60, '...') }}
                                            </a>
                                        </li>
                                    @endforeach
                                    {{-- حالت دوم: فرزندان استاتیک دیتابیس --}}
                                @else
                                    @foreach($menu->children as $child)
                                        @php
                                            $specialClass = $isServices ? match($loop->iteration) {
                                                1 => '1st-drop', 2 => '2nd-drop', 3 => '3rd-drop', default => ''
                                            } : '';
                                        @endphp
                                        <li>
                                            <a class="dropdown-item {{ $specialClass }} fs-costum5-sm p-0 pe-1 d-flex align-items-center"
                                               @if($isServices || $child->new_tab) target="_blank" rel="noopener noreferrer nofollow" @endif
                                               href="{{ $child->route_name ? route($child->route_name) : $child->url }}">
                                                <i class="bi bi-dot"></i> {{ $child->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
