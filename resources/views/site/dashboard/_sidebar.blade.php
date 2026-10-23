@php
    $currentRoute = request()->route()?->getName() ?? '';
    $navItems = [
        ['route' => 'dashboard', 'label' => __('messages.account_info'), 'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
        ['route' => 'dashboard.orders', 'label' => __('messages.my_orders'), 'icon' => '<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
        ['route' => 'dashboard.wishlist', 'label' => __('messages.wishlist'), 'icon' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>'],
        ['route' => 'dashboard.addresses', 'label' => __('messages.addresses'), 'icon' => '<path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 0 1 6 0z"/>'],
        ['route' => 'dashboard.notifications', 'label' => __('messages.notifications'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>'],
    ];
@endphp

<aside class="dashboard-nav space-y-1">
    {{-- هدر سایدبار --}}
    <div class="dashboard-nav-head flex items-center gap-3 px-4 py-3 mb-2">
        <div class="dashboard-nav-avatar w-10 h-10 rounded-2xl grid place-items-center shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="dashboard-nav-name text-sm font-bold truncate">{{ auth()->user()->name }}</p>
            <p class="text-[11px] truncate dashboard-nav-mobile" dir="ltr">{{ auth()->user()->mobile }}</p>
        </div>
    </div>

    {{-- لینک‌های ناوبری --}}
    @foreach($navItems as $item)
        @php
            // Exact match for dashboard index, prefix match for sub-routes
            $isActive = $item['route'] === 'dashboard'
                ? $currentRoute === 'dashboard'
                : ($currentRoute === $item['route'] || str_starts_with($currentRoute, $item['route'] . '.'));
        @endphp
        <a href="{{ route($item['route']) }}"
           class="dashboard-nav-link {{ $isActive ? 'is-active' : '' }}">
            <span class="dashboard-nav-ico">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                    {!! $item['icon'] !!}
                </svg>
            </span>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach

    {{-- خط جداکننده --}}
    <div class="dashboard-nav-sep my-2 mx-4"></div>

    {{-- خروج --}}
    <form action="{{ localizedRoute('logout') }}" method="POST">
        @csrf
        <button type="submit" class="dashboard-nav-link dashboard-nav-logout">
            <span class="dashboard-nav-ico">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </span>
            <span>{{ __('messages.logout') }}</span>
        </button>
    </form>
</aside>
