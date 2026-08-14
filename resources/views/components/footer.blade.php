<footer class="mt-16 py-10" style="background: var(--bg-elevated);">
    <div class="max-w-7xl mx-auto px-4 md:px-6 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="neu-card">
            <h3 class="font-bold mb-2" style="color: var(--accent);">{{ setting('site_name', __('messages.site_name')) }}</h3>
            <p class="text-sm" style="color: var(--text-secondary);">
                {{ setting('footer_description', __('messages.footer_desc') ?? '') }}
            </p>
            @if(setting('phone') || setting('email'))
                <ul class="mt-3 space-y-1.5 text-xs" style="color: var(--text-secondary);">
                    @if(setting('phone'))
                        <li class="flex items-center gap-2">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                            <span dir="ltr">{{ setting('phone') }}</span>
                        </li>
                    @endif
                    @if(setting('email'))
                        <li class="flex items-center gap-2">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                            <span dir="ltr">{{ setting('email') }}</span>
                        </li>
                    @endif
                    @if(setting('address_one'))
                        <li class="flex items-start gap-2">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ setting('address_one') }}
                        </li>
                    @endif
                </ul>
            @endif
            {{-- لینک‌های شبکهٔ اجتماعی فعال (از تنظیمات) — دکمه‌های دایرهای برند --}}
            <div class="mt-4">
                <x-social-links compact />
            </div>
        </div>

        <div class="neu-card">
            <h4 class="font-semibold mb-3">{{ __('messages.quick_links') }}</h4>
            <ul class="space-y-2 text-sm" style="color: var(--text-secondary);">
                <li><a href="{{ localizedRoute('products.index') }}" class="hover:opacity-80 transition-opacity">{{ __('messages.products') }}</a></li>
                <li><a href="{{ localizedRoute('site.page', ['page' => 'about']) }}" class="hover:opacity-80 transition-opacity">{{ __('messages.pages.about.title') }}</a></li>
                <li><a href="{{ localizedRoute('site.page', ['page' => 'contact']) }}" class="hover:opacity-80 transition-opacity">{{ __('messages.pages.contact.title') }}</a></li>
                <li><a href="{{ localizedRoute('site.page', ['page' => 'faq']) }}" class="hover:opacity-80 transition-opacity">{{ __('messages.pages.faq.title') }}</a></li>
                <li><a href="{{ localizedRoute('site.page', ['page' => 'terms']) }}" class="hover:opacity-80 transition-opacity">{{ __('messages.pages.terms.title') }}</a></li>
                <li><a href="{{ localizedRoute('site.page', ['page' => 'privacy']) }}" class="hover:opacity-80 transition-opacity">{{ __('messages.pages.privacy.title') }}</a></li>
            </ul>
        </div>

        <div class="neu-card">
            <h4 class="font-semibold mb-3">{{ __('messages.account_info') }}</h4>
            <ul class="space-y-2 text-sm" style="color: var(--text-secondary);">
                @auth
                    <li><a href="" class="hover:opacity-80">{{ __('messages.dashboard') }}</a></li>
                    <li><a href="" class="hover:opacity-80">{{ __('messages.my_orders') }}</a></li>
                @else
                    <li><a href="" class="hover:opacity-80">{{ __('messages.login') }}</a></li>
                    <li><a href="" class="hover:opacity-80">{{ __('messages.register') }}</a></li>
                @endauth
                <li><a href="{{ localizedRoute('cart.index') }}" class="hover:opacity-80">{{ __('messages.cart') }}</a></li>
            </ul>
        </div>

        <div class="neu-card">
            <h4 class="font-semibold mb-3">{{ setting('footer_newsletter_title', __('messages.newsletter') ?? 'خبرنامه') }}</h4>
            <input type="email" placeholder="{{ setting('newsletter_placeholder', __('messages.search_placeholder') ?? 'you@example.com') }}" class="neu-input">
        </div>
    </div>

    <p class="text-center text-sm mt-8" style="color: var(--text-secondary);">
        &copy; {{ show_date(null, 'Y') }} {{ setting('site_name', __('messages.site_name')) }} — {{ setting('footer_copyright', '') }}
    </p>
</footer>
