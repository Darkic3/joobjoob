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
                        <li class="flex items-center gap-2">📞 <span dir="ltr">{{ setting('phone') }}</span></li>
                    @endif
                    @if(setting('email'))
                        <li class="flex items-center gap-2">✉️ <span dir="ltr">{{ setting('email') }}</span></li>
                    @endif
                    @if(setting('address_one'))
                        <li class="flex items-start gap-2">📍 {{ setting('address_one') }}</li>
                    @endif
                </ul>
            @endif
            @php
                $socials = collect(['social_instagram' => 'اینستاگرام', 'social_telegram' => 'تلگرام', 'social_whatsapp' => 'واتساپ', 'social_twitter' => 'توییتر', 'social_youtube' => 'یوتیوب']);
                $socialLinks = $socials->filter(fn($label, $key) => setting($key))->all();
            @endphp
            @if($socialLinks)
                <div class="flex items-center gap-2 mt-3">
                    @foreach($socialLinks as $key => $label)
                        <a href="{{ setting($key) }}" target="_blank" rel="noopener" title="{{ $label }}"
                           class="neu-btn neu-btn-icon w-8 h-8 text-xs hover:opacity-70">{{ $label[0] }}</a>
                    @endforeach
                </div>
            @endif
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
                @auth
                    <li><a href="" class="hover:opacity-80">{{ __('messages.dashboard') }}</a></li>
                    <li><a href="" class="hover:opacity-80">{{ __('messages.my_orders') }}</a></li>
                @else
                    <li><a href="" class="hover:opacity-80">{{ __('messages.login') }}</a></li>
                    <li><a href="" class="hover:opacity-80">{{ __('messages.register') }}</a></li>
                @endauth
                <li><a href="{{ route('cart.index') }}" class="hover:opacity-80">{{ __('messages.cart') }}</a></li>
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
