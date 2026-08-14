<footer class="site-footer">
    {{-- شل بیرونی: قاب گرادینتی با خط مویی و شعاع بزرگ (Double-Bezel لایهٔ ۱) --}}
    <div class="site-footer-shell">
        {{-- کور داخلی: محتوای اصلی (Double-Bezel لایهٔ ۲) --}}
        <div class="site-footer-core">
            <div class="site-footer-grid">

                {{-- ستون برند — عریض‌تر با اسپیس لوکس --}}
                <div class="site-footer-brand">
                    <div class="site-footer-brand-top">
                        <span class="site-footer-logo-mark" aria-hidden="true">
                            {{ mb_substr(setting('site_name', __('messages.site_name')), 0, 1) }}
                        </span>
                        <div>
                            <p class="site-footer-brand-name">{{ setting('site_name', __('messages.site_name')) }}</p>
                            <p class="site-footer-brand-tag">{{ __('messages.footer_brand_tag') }}</p>
                        </div>
                    </div>

                    <p class="site-footer-desc">
                        {{ setting('footer_description', __('messages.footer_desc') ?? '') }}
                    </p>

                    @if(setting('phone') || setting('email') || setting('address_one'))
                        <ul class="site-footer-contact">
                            @if(setting('phone'))
                                <li>
                                    <span class="site-footer-contact-icon" aria-hidden="true">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                                    </span>
                                    <span dir="ltr">{{ setting('phone') }}</span>
                                </li>
                            @endif
                            @if(setting('email'))
                                <li>
                                    <span class="site-footer-contact-icon" aria-hidden="true">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                                    </span>
                                    <span dir="ltr">{{ setting('email') }}</span>
                                </li>
                            @endif
                            @if(setting('address_one'))
                                <li>
                                    <span class="site-footer-contact-icon" aria-hidden="true">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </span>
                                    {{ setting('address_one') }}
                                </li>
                            @endif
                        </ul>
                    @endif

                    <div class="site-footer-social">
                        <x-social-links compact />
                    </div>
                </div>

                {{-- ستون دسترسی سریع — آکاردئون در موبایل (بدون JS: همیشه باز) --}}
                <nav class="site-footer-col site-footer-acc" data-footer-acc aria-label="{{ __('messages.quick_links') }}">
                    <button type="button" class="site-footer-acc-btn"
                            aria-expanded="false" aria-controls="footer-acc-quick"
                            data-footer-acc-toggle>
                        <h4 class="site-footer-heading">
                            <span class="site-footer-heading-dot" aria-hidden="true"></span>
                            {{ __('messages.quick_links') }}
                        </h4>
                        <svg class="site-footer-acc-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="site-footer-acc-body" id="footer-acc-quick">
                        <div class="site-footer-acc-inner">
                        <ul class="site-footer-links">
                            <li><a href="{{ localizedRoute('products.index') }}">{{ __('messages.products') }}</a></li>
                            <li><a href="{{ localizedRoute('site.page', ['page' => 'about']) }}">{{ __('messages.pages.about.title') }}</a></li>
                            <li><a href="{{ localizedRoute('site.page', ['page' => 'contact']) }}">{{ __('messages.pages.contact.title') }}</a></li>
                            <li><a href="{{ localizedRoute('site.page', ['page' => 'faq']) }}">{{ __('messages.pages.faq.title') }}</a></li>
                            <li><a href="{{ localizedRoute('site.page', ['page' => 'terms']) }}">{{ __('messages.pages.terms.title') }}</a></li>
                            <li><a href="{{ localizedRoute('site.page', ['page' => 'privacy']) }}">{{ __('messages.pages.privacy.title') }}</a></li>
                        </ul>
                        </div>
                    </div>
                </nav>

                {{-- ستون اطلاعات حساب — آکاردئون در موبایل --}}
                <nav class="site-footer-col site-footer-acc" data-footer-acc aria-label="{{ __('messages.account_info') }}">
                    <button type="button" class="site-footer-acc-btn"
                            aria-expanded="false" aria-controls="footer-acc-account"
                            data-footer-acc-toggle>
                        <h4 class="site-footer-heading">
                            <span class="site-footer-heading-dot" aria-hidden="true"></span>
                            {{ __('messages.account_info') }}
                        </h4>
                        <svg class="site-footer-acc-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="site-footer-acc-body" id="footer-acc-account">
                        <div class="site-footer-acc-inner">
                        <ul class="site-footer-links">
                            @auth
                                <li><a href="{{ localizedRoute('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                                <li><a href="{{ localizedRoute('dashboard.orders') }}">{{ __('messages.my_orders') }}</a></li>
                                <li><a href="{{ localizedRoute('dashboard.wishlist') }}">{{ __('messages.wishlist') }}</a></li>
                            @else
                                <li><a href="{{ localizedRoute('login') }}">{{ __('messages.login') }}</a></li>
                                {{-- ثبت‌نام هنوز ساخته نشده — با فعال‌شدن، روت واقعی اینجا قرار می‌گیرد --}}
                                <li><a href="{{ localizedRoute('login') }}">{{ __('messages.register') }}</a></li>
                            @endauth
                            <li><a href="{{ localizedRoute('cart.index') }}">{{ __('messages.cart') }}</a></li>
                        </ul>
                        </div>
                    </div>
                </nav>

                {{-- ستون خبرنامه — آکاردئون در موبایل --}}
                <div class="site-footer-col site-footer-acc" data-footer-acc>
                    <button type="button" class="site-footer-acc-btn"
                            aria-expanded="false" aria-controls="footer-acc-newsletter"
                            data-footer-acc-toggle>
                        <h4 class="site-footer-heading">
                            <span class="site-footer-heading-dot" aria-hidden="true"></span>
                            {{ setting('footer_newsletter_title', __('messages.newsletter') ?? 'خبرنامه') }}
                        </h4>
                        <svg class="site-footer-acc-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="site-footer-acc-body" id="footer-acc-newsletter">
                        <div class="site-footer-acc-inner">
                            <p class="site-footer-newsletter-sub">{{ __('messages.newsletter_subtitle') }}</p>
                            <form class="site-footer-newsletter" action="#" method="post" onsubmit="return false;">
                                <input type="email" name="email" required
                                       placeholder="{{ setting('newsletter_placeholder', __('messages.search_placeholder') ?? 'you@example.com') }}"
                                       class="site-footer-newsletter-input" aria-label="{{ __('messages.newsletter') }}">
                                <button type="submit" class="site-footer-newsletter-btn" aria-label="{{ __('messages.newsletter') }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- نوار پایین: حق‌کپی با خط مویی جداکننده --}}
            <div class="site-footer-bottom">
                <p>&copy; {{ show_date(null, 'Y') }} {{ setting('site_name', __('messages.site_name')) }} — {{ setting('footer_copyright', '') }}</p>
            </div>
        </div>
    </div>
</footer>
