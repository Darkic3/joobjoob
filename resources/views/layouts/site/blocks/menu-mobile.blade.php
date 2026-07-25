<div class="navbar menu-desktop navbar-expand-lg position-sticky top-0 ice-navbar z-99 d-none d-lg-block">
    <div class="container">
        <button class="navbar-toggler px-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="offcanvas offcanvas-start bg-body-tertiary" tabindex="-1" id="offcanvasNavbar">
            <div class="offcanvas-header">
                <img src="{{ $setting->imageFile('logo') }}" width="130" alt="exir logo">
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body">
                {{-- لینک صفحه اصلی --}}
                <a class="nav-link fw-bolder py-3 {{ request()->routeIs('site.home-page.index') ? 'active' : '' }}"
                   href="{{ route('site.home-page.index') }}">
                    <span
                        class="{{ request()->is('/') ? 'text-red-custom' : 'text-blue-custom' }} fs-costum3-sm-2 fw-bolder">
                        <img src="{{ asset('assets/site/images/home.svg') }}" width="20" alt="home"> صفحه اصلی
                    </span>
                </a>

                <div class="accordion" id="accordionMenu">
                    @foreach($menu_items_show as $menu)
                        @php
                            $menuIcon = match($menu->name) {
                                'خدمات آنلاین', 'اخبار و مقالات' => 'services.svg',
                                'آگهی ها' => 'news (1).svg',
                                'مدیران', 'درباره ما' => 'about-us.svg',
                                'تماس با ما' => 'phone-menu copy.svg',
                                default => 'services.svg'
                            };
                            $isAds = ($menu->type == 'dyno' && $menu->name == 'آگهی ها');
                            $hasChildren = ($menu->children->count() > 0 || ($isAds && isset($ads_menu_cat)));
                            $showDropdown = ($menu->drop && $hasChildren);
                            $collapseId = "collapse-" . $menu->id;
                        @endphp

                        @if($showDropdown)
                            <div class="accordion-item border-0">
                                <span class="accordion-header">
                                    <button
                                        class="accordion-button collapsed px-0 text-blue-custom fs-costum3-sm-2 bg-body-tertiary shadow-none"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                        <img src="{{ asset('assets/site/images/' . $menuIcon) }}" class="pe-2"
                                             width="35" alt="icon">
                                        {{ $menu->name }}
                                    </button>
                                </span>
                                <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                     data-bs-parent="#accordionMenu">
                                    <div class="accordion-body p-0 m-0 pt-2 ps-4">
                                        <ul class="list-unstyled">
                                            @if($isAds)
                                                @foreach ($ads_menu_cat as $cat)
                                                    <li class="py-2">
                                                        <a href="{{ route('site.ads.list', $cat->url) }}"
                                                           class="text-decoration-none text-grey-custom fs-costum3-sm-2">
                                                            {{ Str::limit($cat->title, 60, '...') }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @else
                                                @foreach($menu->children as $child)
                                                    <li class="py-2">
                                                        <a href="{{ $child->route_name ? route($child->route_name) : $child->url }}"
                                                           @if($menu->name == 'خدمات آنلاین' || $child->new_tab) target="_blank"
                                                           rel="noopener noreferrer nofollow" @endif
                                                           class="text-decoration-none text-grey-custom fs-costum3-sm-2">
                                                            {{ $child->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-body-tertiary">
                                <a href="{{ $menu->route_name ? route($menu->route_name) : ($menu->url ?? '#') }}"
                                   @if($menu->new_tab) target="_blank" rel="noopener noreferrer" @endif
                                   class="text-decoration-none text-blue-custom py-3 px-0 border-0 d-block fs-costum3-sm-2">
                                    <img src="{{ asset('assets/site/images/' . $menuIcon) }}" class="pe-2" width="35"
                                         alt="icon">
                                    {{ $menu->name }}
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <a class="navbar-brand" href="{{ route('site.home-page.index') }}">
            <img src="{{ $setting->imageFile('logo') }}" width="140" alt="exir logo">
        </a>
    </div>
</div>
