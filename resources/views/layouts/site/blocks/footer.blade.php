<div class="footer border-y-red">
    <div class="pb-3">
        <div class="container pt-5">
            <div class="row m-0 w-100">

                <div class="col-12 col-lg-4  divider-after position-relative pb-lg-4 mb-2 px-0">
                    @if ($setting->getSetting('address'))
                        <img src="{{ asset('assets/site/images/loc-white.svg') }}" width="20" alt="location">
                        <span class="text-white fs-costum3-sm-2">
                            آدرس :
                        </span>
                        <p class="text-white  fs-costum2">
                            {{ $setting->getSetting('address') }}
                        </p>
                    @endif

                    <p class="text-white text-white lh-md fs-costum5-sm txt-limited-6 pe-5">
                        {{ $setting->getSetting('footer_description') }}
                    </p>
                    <div class="d-lg-flex d-none gap-2 align-items-center">

                        @if ($setting->getSetting('linkedin'))
                            <a href="{{ $setting->getSetting('linkedin') }}" rel="nofollow noreferrer" target="_blank">
                                <img src="{{ asset('assets/site/images/linkedin-square 1.svg') }}" alt="social"
                                    width="35">
                            </a>
                        @endif
                        @if ($setting->getSetting('instagram'))
                            <a href="{{ $setting->getSetting('instagram') }}" rel="nofollow noreferrer" target="_blank">
                                <img src="{{ asset('assets/site/images/instagram-square 1.svg') }}" alt="social"
                                    width="35">

                            </a>
                        @endif
                        @if ($setting->getSetting('telegram'))
                            <a href="{{ $setting->getSetting('telegram') }}" rel="nofollow noreferrer" target="_blank">
                                <img src="{{ asset('assets/site/images/telegram-square 1.svg') }}" alt="social"
                                    width="35">

                            </a>
                        @endif
                        @if ($setting->getSetting('whatsapp'))
                            <a href="{{ $setting->getSetting('whatsapp') }}">
                                <img src="{{ asset('assets/site/images/whatsapp-square 1 (1).svg') }}" alt="social"
                                    width="35">

                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-3 d-none d-lg-block col-lg-2 position-relative divider-after">
                    @if ($setting->getSetting('phone_two'))
                        <div class="text-white fs-costum3-sm-2 mb-2">
                            <img src="{{ asset('assets/site/images/phone-white.svg') }}" width="12" alt="phone">
                            <span>
                                تلفن:
                            </span>
                            <span class="fs-costum2 py-2 d-block text-white f-light">
                                <a href="tel:+{{ $setting->getSetting('phone_two') }}"
                                    class="text-decoration-none text-white">
                                    {{ $setting->getSetting('phone_two') }}

                                </a>
                            </span>
                        </div>
                    @endif
                    <ul class="list-unstyled">
                        <li class="mb-3 mt-4">
                            <a href="{{ route('site.teams') }}"
                                class="text-white text-decoration-none fs-costum2 pb-4">
                                اعضای هیئت مدیره
                            </a>
                        </li>
                        <li class="pb-1">
                            <a href="{{ route('site.contact-us') }}"
                                class="text-white text-decoration-none fs-costum5-sm">
                                ارتباط با ما
                            </a>
                        </li>
                        <li class="pb-1">
                            <a href="{{ route('site.about-us') }}"
                                class="text-white text-decoration-none fs-costum5-sm">
                                درباره ما
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2 col-md-3 divider-after position-relative pe-0">
                    @if ($setting->getSetting('phone_one'))
                        <div class="text-white fs-costum3-sm-2 d-lg-flex d-none gap-2">
                            <img src="{{ asset('assets/site/images/phone-white.svg') }}" width="12" alt="phone">
                            <span>
                                تلفن:
                            </span>
                        </div>
                        <span class="fs-costum2 py-2 text-white f-light d-none d-lg-block">
                            <a href="tel:+{{ $setting->getSetting('phone_one') }}"
                                class="text-decoration-none text-white">
                                {{ $setting->getSetting('phone_one') }}
                            </a>
                        </span>
                    @endif
                    <ul class="list-unstyled">
                        <li class="mb-3 mt-4">
                            <a href="{{ route('site.home-page.index') }}"
                                class="text-white text-decoration-none fs-costum2 pb-4">
                                خانه
                            </a>
                        </li>
                        <li class="pb-1">
                            <a href="{{ route('site.news.list') }}"
                                class="text-white text-decoration-none fs-costum5-sm">
                                اخبار
                            </a>
                        </li>
                        <li class="pb-1">
                            <a href="{{ route('site.reports.list') }}"
                                class="text-white text-decoration-none fs-costum5-sm">
                                گزارشات تصویری
                            </a>
                        </li>
                        <li class="pb-1">
                            <a href="{{ route('site.blog.list') }}"
                                class="text-white text-decoration-none fs-costum5-sm">
                                مقالات
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2 col-md-3 divider-after position-relative pe-0">
                    @if ($setting->getSetting('fax'))
                        <div class="text-white fs-costum3-sm-2 d-lg-flex d-none gap-2">
                            <img src="{{ asset('assets/site/images/phone (1).svg') }}" width="13" alt="phone">
                            <span>فکس:</span>
                        </div>
                        <span class="fs-costum2 py-2  text-white d-lg-block d-none f-light">
                            <a href="tel:+{{ $setting->getSetting('fax') }}" class="text-decoration-none text-white">
                                {{ $setting->getSetting('fax') }}
                            </a>
                        </span>
                    @endif
                    <ul class="list-unstyled">
                        <li class="mb-3 mt-4">
                            <a class="text-white text-decoration-none fs-costum2 pb-4">
                                آگهی ها
                            </a>
                        </li>
                        @foreach ($ads_menu_cat as $cat)
                            <li class="pb-1">
                                <a href="{{ route('site.ads.list', $cat->url) }}"
                                    class="text-white text-decoration-none fs-costum5-sm">
                                    {{ Str::limit($cat->title, 60, '...') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-6 col-lg-2  col-md-3">
                    @if ($setting->getSetting('email'))
                        <div class="text-white fs-costum3-sm-2 d-lg-flex  d-none gap-2">
                            <img src="{{ asset('assets/site/images/mail-white.svg') }}" width="12" alt="phone">
                            <span>
                                ایمیل:
                            </span>
                        </div>
                        <span class="fs-costum2 py-2  text-white f-light d-lg-block d-none">
                            <a href="mailto:{{ $setting->getSetting('email') }}"
                                class="text-decoration-none text-white">
                                {{ $setting->getSetting('email') }}
                            </a>
                        </span>
                    @endif
                    <div class="row row-cols-2 d-none d-lg-flex g-3 logos-footer mt-2">
                        @foreach ($logos as $desktopLogo)
                            <div class="col">
                                <a target='_blank' href="{{ $desktopLogo->link }}">
                                    <img src="{{ $desktopLogo->getImageFile() }}" alt="logo">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div
                    class="col-6 col-md-3  gap-3  justify-content-center flex-wrap py-3 d-lg-none divider-after position-relative">
                    @if ($setting->getSetting('phone_two'))
                        <div class="text-white fs-costum3-sm-2  gap-2">
                            <img src="{{ asset('assets/site/images/phone-white.svg') }}" width="12"
                                alt="phone">
                            <span>
                                تلفن:
                            </span>
                            <span class="fs-costum2 py-2 d-block text-white f-light">
                                <a href="tel:+{{ $setting->getSetting('phone_two') }}"
                                    class="text-decoration-none text-white">
                                    {{ $setting->getSetting('phone_two') }}
                                </a>
                            </span>

                        </div>
                    @endif
                    @if ($setting->getSetting('fax'))
                        <div class="text-white fs-costum3-sm-2 d-lg-flex gap-2">
                            <img src="{{ asset('assets/site/images/phone (1).svg') }}" width="13"
                                alt="phone">
                            <span>
                                فکس:
                            </span>
                            <span class="fs-costum2 py-2 d-block text-white f-light">
                                <a href="tel:+{{ $setting->getSetting('fax') }}"
                                    class="text-decoration-none text-white">
                                    {{ $setting->getSetting('fax') }}
                                </a>
                            </span>
                        </div>
                    @endif
                    @if ($setting->getSetting('email'))
                        <div class="text-white fs-costum3-sm-2 d-lg-flex gap-2">
                            <img src="{{ asset('assets/site/images/mail-white.svg') }}" width="12"
                                alt="phone">
                            <span>
                                ایمیل:
                            </span>
                            <span class="fs-costum2 py-2 d-block text-white f-light">
                                <a href="mailto: {{ $setting->getSetting('email') }}"
                                    class="text-decoration-none text-white fs-costum2 py-2 d-block text-white f-light">
                                    {{ $setting->getSetting('email') }}
                                </a>
                            </span>
                        </div>
                    @endif

                    <div class="gap-2 align-items-center">
                        @if ($setting->getSetting('linkedin'))
                            <a href="{{ $setting->getSetting('linkedin') }}">
                                <img src="{{ asset('assets/site/images/linkedin-square 1.svg') }}" alt="social"
                                    width="35">
                            </a>
                        @endif
                        @if ($setting->getSetting('instagram'))
                            <a href="{{ $setting->getSetting('instagram') }}">
                                <img src="{{ asset('assets/site/images/instagram-square 1.svg') }}" alt="social"
                                    width="35">

                            </a>
                        @endif
                        @if ($setting->getSetting('telegram'))
                            <a href="{{ $setting->getSetting('telegram') }}">
                                <img src="{{ asset('assets/site/images/telegram-square 1.svg') }}" alt="social"
                                    width="35">

                            </a>
                        @endif
                        @if ($setting->getSetting('whatsapp'))
                            <a href="{{ $setting->getSetting('whatsapp') }}">
                                <img src="{{ asset('assets/site/images/whatsapp-square 1 (1).svg') }}" alt="social"
                                    width="35">

                            </a>
                        @endif

                    </div>
                </div>
                <div class="col-12 px-0 d-flex d-lg-none ">
                    <div class="row row-cols-4 g-3 pt-3 pb-5">
                        @foreach ($logos as $logo)
                            <div class="col">
                                <a target='_blank' href="{{ $logo->link }}">
                                    <img src="{{ $logo->getImageFile() }}" alt="logo" class="rounded-4 w-100">
                                </a>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="text-center mx-auto my-2">
    <span class="fw-bolder text-red-custom fs-costum3-sm-2">
        کلیه حقوق این سایت متعلق به شرکت
    </span>
    <span class="fw-bolder text-blue-custom-2 fs-costum3-sm-2">
        پخش دارویی اکسیر
    </span>
    <span class="fw-bolder text-red-custom fs-costum3-sm-2">
        میباشد.
    </span>
    <a href="https://www.rahweb.com/" class="text-grey-custom-3 fw-bolder fs-costum2 text-decoration-none d-block">
        طراحی سایت: شرکت ره وب
    </a>
</div>
