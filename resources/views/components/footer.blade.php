<footer class="mt-16 py-10" style="background: var(--bg-elevated);">
    <div class="max-w-7xl mx-auto px-4 md:px-6 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="neu-card">
            <h3 class="font-bold mb-2" style="color: var(--accent);">{{ __('messages.site_name') }}</h3>
            <p class="text-sm" style="color: var(--text-secondary);">
                {{ __('messages.footer_desc') ?? 'فروشگاه آنلاین با بهترین کیفیت و قیمت' }}
            </p>
        </div>

        <div class="neu-card">
            <h4 class="font-semibold mb-3">{{ __('messages.categories') }}</h4>
            <ul class="space-y-2 text-sm" style="color: var(--text-secondary);">
                <li><a href="{{ route('products.index') }}" class="hover:opacity-80">{{ __('messages.products') }}</a></li>
            </ul>
        </div>

        <div class="neu-card">
            <h4 class="font-semibold mb-3">{{ __('messages.account_info') }}</h4>
            <ul class="space-y-2 text-sm" style="color: var(--text-secondary);">
                <li><a href="{{ route('dashboard') }}" class="hover:opacity-80">{{ __('messages.dashboard') }}</a></li>
                <li><a href="{{ route('cart.index') }}" class="hover:opacity-80">{{ __('messages.cart') }}</a></li>
            </ul>
        </div>

        <div class="neu-card">
            <h4 class="font-semibold mb-3">{{ __('messages.newsletter') ?? 'خبرنامه' }}</h4>
            <input type="email" placeholder="you@example.com" class="neu-input">
        </div>
    </div>

    <p class="text-center text-sm mt-8" style="color: var(--text-secondary);">
        &copy; {{ date('Y') }} {{ __('messages.site_name') }}
    </p>
</footer>
