<section class="reveal-on-scroll py-6 mb-4">
    <div class="neu-flat-lg p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="text-center md:text-start">
            <h3 class="font-extrabold text-lg mb-2">{{ setting('newsletter_title', '۱۰٪ تخفیف خرید اول، هدیه‌ی ماست') }}</h3>
            <p class="text-xs" style="color: var(--text-secondary);">{{ setting('newsletter_desc', 'با عضویت در خبرنامه، کد تخفیف رو همین الان بگیر') }}</p>
        </div>
        <form class="flex gap-2 w-full md:w-auto" action="#" method="POST">
            @csrf
            <input type="email" name="email" placeholder="{{ setting('newsletter_placeholder', 'ایمیل شما') }}"
                   class="neu-input px-4 py-3 text-sm flex-1 md:w-64">
            <button type="submit" class="neu-btn neu-btn-primary px-6 py-3 text-sm font-bold whitespace-nowrap">
                {{ setting('newsletter_button', 'عضویت') }}
            </button>
        </form>
    </div>
</section>
