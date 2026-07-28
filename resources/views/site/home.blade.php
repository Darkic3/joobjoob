<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>فروشگاه جامع اینترنتی | خرید مطمئن</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased">

{{-- نوار بالا (Navbar) --}}
<nav class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center gap-4 h-16">
            <a href="#" class="text-xl font-bold text-gray-900 shrink-0">مارکت<span class="text-indigo-600">پلاس</span></a>

            {{-- نوار جستجو --}}
            <div class="flex-1 max-w-xl relative">
                <input type="text" placeholder="جستجو در میان هزاران محصول..." class="w-full bg-gray-100 border border-transparent focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100 rounded-xl py-2.5 pr-10 pl-4 text-sm transition-all outline-none">
                <svg class="w-5 h-5 text-gray-400 absolute top-3 right-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="absolute top-1 left-1 bg-red-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full">۳</span>
                </button>
                <button class="hidden sm:flex items-center gap-2 bg-gray-900 text-white text-sm px-4 py-2.5 rounded-xl hover:bg-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    ورود | ثبت‌نام
                </button>
            </div>
        </div>

        {{-- منوی دسته‌بندی‌ها --}}
        <div class="flex items-center gap-4 py-2 text-sm font-medium text-gray-600 border-t border-gray-50 overflow-x-auto no-scrollbar">
            <a href="#" class="flex items-center gap-1 hover:text-indigo-600 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                دسته‌بندی محصولات
            </a>
            <a href="#" class="hover:text-indigo-600 whitespace-nowrap">کالای دیجیتال</a>
            <a href="#" class="hover:text-indigo-600 whitespace-nowrap">خودرو، ابزار و تجهیزات صنعتی</a>
            <a href="#" class="hover:text-indigo-600 whitespace-nowrap">مد و پوشاک</a>
            <a href="#" class="hover:text-indigo-600 whitespace-nowrap">آشپزخانه</a>
            <a href="#" class="hover:text-indigo-600 whitespace-nowrap">زیبایی و سلامت</a>
            <a href="#"="hover:text-indigo-600 whitespace-nowrap">اسباب بازی</a>
        </div>
    </div>
</nav>

<!-- سیگنال‌های اعتماد (Trust Badges) -->
<div class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 grid grid-cols-2 md:grid-cols-4 gap-4 text-xs sm:text-sm">
        <div class="flex items-center gap-2 justify-center md:justify-start">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <span>ضمانت اصالت کالا</span>
        </div>
        <div class="flex items-center gap-2 justify-center md:justify-start">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>ارسال سریع و بسته‌بندی امن</span>
        </div>
        <div class="flex items-center gap-2 justify-center md:justify-start">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            <span>پرداخت در محل (نقدی/کارتی)</span>
        </div>
        <div class="flex items-center gap-2 justify-center md:justify-start">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            <span>۷ روز ضمانت بازگشت</span>
        </div>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- بخش هیرو و بنرها (Bento Grid) --}}
    <section class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-8">
        <div class="lg:col-span-3 bg-gray-900 rounded-3xl p-8 md:p-12 text-white relative overflow-hidden h-64 md:h-80 flex flex-col justify-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 relative z-10">همه چیز، یک کلیک دورتر از شماست</h2>
            <p class="text-gray-400 max-w-lg relative z-10 mb-6">از جدیدترین گجت‌های دیجیتال تا لوازم روزمره خانه، با بهترین قیمت و ضمانت اصالت کالا.</p>
            <a href="#" class="bg-white text-gray-900 font-bold px-6 py-3 rounded-xl w-fit hover:bg-gray-200 transition relative z-10">شروع خرید</a>
            <div class="absolute left-0 bottom-0 w-2/3 h-full opacity-30 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?q=80&w=2070&auto=format&fit=crop');"></div>
        </div>
        <div class="hidden lg:flex flex-col gap-4">
            <div class="bg-indigo-50 rounded-3xl p-6 flex-1 flex flex-col justify-center hover:shadow-md transition">
                <h3 class="font-bold text-indigo-900 text-lg">تخفیف‌های روزانه</h3>
                <p class="text-indigo-500 text-sm mt-1">فرصت محدود!</p>
                <a href="#" class="text-indigo-600 font-semibold text-sm mt-4">مشاهده ←</a>
            </div>
            <div class="bg-amber-50 rounded-3xl p-6 flex-1 flex flex-col justify-center hover:shadow-md transition">
                <h3 class="font-bold text-amber-900 text-lg">کلوب مشتریان</h3>
                <p class="text-amber-500 text-sm mt-1">جمع کن، جایزه بگیر</p>
                <a href="#" class="text-amber-600 font-semibold text-sm mt-4">عضویت ←</a>
            </div>
        </div>
    </section>

    {{-- دسته‌بندی‌های سریع (دایره‌ای) --}}
    <section class="mb-8 bg-white p-6 rounded-2xl border border-gray-100">
        <div class="grid grid-cols-4 md:grid-cols-8 gap-4 text-center">
            <a href="#" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-100 group-hover:bg-indigo-50 p-3 transition-all">
                    <img src="https://api.iconify.design/fluent-emoji/laptop.svg" alt="دیجیتال">
                </div>
                <span class="text-xs md:text-sm font-medium text-gray-700">کالای دیجیتال</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-100 group-hover:bg-indigo-50 p-3 transition-all">
                    <img src="https://api.iconify.design/fluent-emoji/t-shirt.svg" alt="پوشاک">
                </div>
                <span class="text-xs md:text-sm font-medium text-gray-700">مد و پوشاک</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-100 group-hover:bg-indigo-50 p-3 transition-all">
                    <img src="https://api.iconify.design/fluent-emoji/shopping-bags.svg" alt="خانگی">
                </div>
                <span class="text-xs md:text-sm font-medium text-gray-700">خانه و آشپزخانه</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-100 group-hover:bg-indigo-50 p-3 transition-all">
                    <img src="https://api.iconify.design/fluent-emoji/soap-bubble.svg" alt="زیبایی">
                </div>
                <span class="text-xs md:text-sm font-medium text-gray-700">زیبایی و سلامت</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-100 group-hover:bg-indigo-50 p-3 transition-all">
                    <img src="https://api.iconify.design/fluent-emoji/automobile.svg" alt="خودرو">
                </div>
                <span class="text-xs md:text-sm font-medium text-gray-700">ابزار و خودرو</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-100 group-hover:bg-indigo-50 p-3 transition-all">
                    <img src="https://api.iconify.design/fluent-emoji/baby.svg" alt="اسباب بازی">
                </div>
                <span class="text-xs md:text-sm font-medium text-gray-700">اسباب بازی</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-100 group-hover:bg-indigo-50 p-3 transition-all">
                    <img src="https://api.iconify.design/fluent-emoji/bicycle.svg" alt="ورزش">
                </div>
                <span class="text-xs md:text-sm font-medium text-gray-700">ورزش و سفر</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-100 group-hover:bg-indigo-50 p-3 transition-all">
                    <img src="https://api.iconify.design/fluent-emoji/office-building.svg" alt="سایر">
                </div>
                <span class="text-xs md:text-sm font-medium text-gray-700">سایر دسته‌ها</span>
            </a>
        </div>
    </section>

    {{-- فروش ویژه (Flash Sale) --}}
    <section class="mb-8 bg-red-50 rounded-2xl border border-red-100 overflow-hidden">
        <div class="flex justify-between items-center bg-red-500 text-white px-6 py-4">
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-3 2.121-4.5C12.5 9 13 10 13 11s.5 1.5.5 2.5-1.38 1.62-1.38 1.62z" clip-rule="evenodd"></path></svg>
                <h2 class="text-xl font-bold">شگفت‌انگیزهای روز</h2>
            </div>
            <div x-data="{ time: 3600, timer: null, display: '01:00:00' }" x-init="timer = setInterval(() => { if(time > 0) { time--; let h = Math.floor(time/3600).toString().padStart(2, '0'); let m = Math.floor((time%3600)/60).toString().padStart(2, '0'); let s = (time%60).toString().padStart(2, '0'); display = `${h}:${m}:${s}` } }, 1000)" class="bg-white/20 px-4 py-1.5 rounded-lg font-mono tracking-wider text-lg" x-text="display"></div>
        </div>
        <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- محصول شگفت انگیز 1 -->
            <div class="bg-white rounded-xl p-3 border border-gray-100 relative group">
                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">۳۵٪</span>
                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=800&auto=format&fit=crop" class="w-full h-32 object-contain mb-3" alt="هدفون">
                <h3 class="text-sm font-medium truncate">هدفون بی‌سیم اور-ایر</h3>
                <div class="flex items-center gap-1 mt-1 text-amber-400">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span class="text-xs text-gray-500">۴.۵ (۱۲۰)</span>
                </div>
                <div class="mt-2 text-left">
                    <span class="text-gray-400 text-xs line-through block">۲,۸۰۰,۰۰۰</span>
                    <span class="text-gray-900 font-bold text-sm">۱,۸۲۰,۰۰۰ تومان</span>
                </div>
            </div>
            <!-- محصول شگفت انگیز 2 -->
            <div class="bg-white rounded-xl p-3 border border-gray-100 relative group">
                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">۲۰٪</span>
                <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?q=80&w=800&auto=format&fit=crop" class="w-full h-32 object-contain mb-3" alt="ساعت">
                <h3 class="text-sm font-medium truncate">ساعت هوشمند ورزشی</h3>
                <div class="flex items-center gap-1 mt-1 text-amber-400">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span class="text-xs text-gray-500">۴.۸ (۳۴۰)</span>
                </div>
                <div class="mt-2 text-left">
                    <span class="text-gray-400 text-xs line-through block">۵,۰۰۰,۰۰۰</span>
                    <span class="text-gray-900 font-bold text-sm">۴,۰۰۰,۰۰۰ تومان</span>
                </div>
            </div>
            <!-- محصول شگفت انگیز 3 -->
            <div class="bg-white rounded-xl p-3 border border-gray-100 relative group">
                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">۵۰٪</span>
                <img src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad?q=80&w=800&auto=format&fit=crop" class="w-full h-32 object-contain mb-3" alt="عطر">
                <h3 class="text-sm font-medium truncate">عطر و ادکلن مردانه</h3>
                <div class="flex items-center gap-1 mt-1 text-amber-400">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span class="text-xs text-gray-500">۴.۲ (۸۹)</span>
                </div>
                <div class="mt-2 text-left">
                    <span class="text-gray-400 text-xs line-through block">۱,۲۰۰,۰۰۰</span>
                    <span class="text-gray-900 font-bold text-sm">۶۰۰,۰۰۰ تومان</span>
                </div>
            </div>
            <!-- محصول شگفت انگیز 4 -->
            <div class="bg-white rounded-xl p-3 border border-gray-100 relative group">
                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">۱۵٪</span>
                <img src="https://images.unsplash.com/photo-1585341757858-e93dba2c7bd2?q=80&w=800&auto=format&fit=crop" class="w-full h-32 object-contain mb-3" alt="کفش">
                <h3 class="text-sm font-medium truncate">کفش کوهنوردی حرفه‌ای</h3>
                <div class="flex items-center gap-1 mt-1 text-amber-400">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span class="text-xs text-gray-500">۴.۷ (۲۱۰)</span>
                </div>
                <div class="mt-2 text-left">
                    <span class="text-gray-400 text-xs line-through block">۳,۵۰۰,۰۰۰</span>
                    <span class="text-gray-900 font-bold text-sm">۲,۹۷۵,۰۰۰ تومان</span>
                </div>
            </div>
        </div>
    </section>

    {{-- بنر تبلیغاتی دوگانه (Bento Promo) --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="bg-gray-900 text-white rounded-2xl p-8 flex items-center justify-between overflow-hidden">
            <div class="z-10">
                <h3 class="text-xl font-bold mb-2">لپ‌تاپ و کامپیوتر</h3>
                <p class="text-gray-400 text-sm mb-4">شگفتی‌های دیجیتال منتظر شماست</p>
                <a href="#" class="text-indigo-400 font-bold text-sm">مشاهده محصولات ←</a>
            </div>
            <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=400&auto=format&fit=crop" class="w-32 h-32 object-cover rounded-xl opacity-80" alt="لپ‌تاپ">
        </div>
        <div class="bg-indigo-600 text-white rounded-2xl p-8 flex items-center justify-between overflow-hidden">
            <div class="z-10">
                <h3 class="text-xl font-bold mb-2">لوازم خانگی</h3>
                <p class="text-indigo-100 text-sm mb-4">راحتی در خانه را تجربه کنید</p>
                <a href="#" class="bg-white text-indigo-600 font-bold text-sm px-4 py-2 rounded-lg">خرید کنید</a>
            </div>
            <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?q=80&w=400&auto=format&fit=crop" class="w-32 h-32 object-cover rounded-xl opacity-80" alt="خانگی">
        </div>
    </section>

    {{-- پر فروش ترین ها --}}
    <section>
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
            <h2 class="text-xl font-bold text-gray-900">پرفروش‌ترین محصولات</h2>
            <a href="#" class="text-sm text-indigo-600 font-medium">مشاهده همه ←</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- کارت محصول 1 -->
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden group transition hover:shadow-lg">
                <div class="p-4 bg-gray-50 flex justify-center items-center h-44">
                    <img src="https://images.unsplash.com/photo-1574180045827-681f8a1a9622?q=80&w=600&auto=format&fit=crop" class="h-full object-contain group-hover:scale-110 transition duration-300" alt="جاروبرقی">
                </div>
                <div class="p-4">
                    <h3 class="font-medium text-gray-800 text-sm truncate mb-1">جاروبرقی بی‌سیم قدرتمند</h3>
                    <div class="flex items-center gap-1 mb-2 text-amber-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <span class="text-xs text-gray-500">۴.۶ (۸۹)</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-gray-900 font-bold text-sm block">۳,۹۰۰,۰۰۰ ت</span>
                        </div>
                        <button class="bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white p-2 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- کارت محصول 2 -->
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden group transition hover:shadow-lg">
                <div class="p-4 bg-gray-50 flex justify-center items-center h-44">
                    <img src="https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?q=80&w=600&auto=format&fit=crop" class="h-full object-contain group-hover:scale-110 transition duration-300" alt="لپ‌تاپ">
                </div>
                <div class="p-4">
                    <h3 class="font-medium text-gray-800 text-sm truncate mb-1">لپ‌تاپ گیمینگ ۱۵ اینچ</h3>
                    <div class="flex items-center gap-1 mb-2 text-amber-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <span class="text-xs text-gray-500">۴.۹ (۵۳۰)</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-900 font-bold text-sm block">۳۸,۰۰۰,۰۰۰ ت</span>
                        <button class="bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white p-2 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- کارت محصول 3 -->
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden group transition hover:shadow-lg">
                <div class="p-4 bg-gray-50 flex justify-center items-center h-44">
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600&auto=format&fit=crop" class="h-full object-contain group-hover:scale-110 transition duration-300" alt="کفش">
                </div>
                <div class="p-4">
                    <h3 class="font-medium text-gray-800 text-sm truncate mb-1">کفش اسپرت روزمره</h3>
                    <div class="flex items-center gap-1 mb-2 text-amber-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <span class="text-xs text-gray-500">۴.۳ (۴۲)</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-900 font-bold text-sm block">۱,۵۰۰,۰۰۰ ت</span>
                        <button class="bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white p-2 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- کارت محصول 4 -->
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden group transition hover:shadow-lg">
                <div class="p-4 bg-gray-50 flex justify-center items-center h-44">
                    <img src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad?q=80&w=600&auto=format&fit=crop" class="h-full object-contain group-hover:scale-110 transition duration-300" alt="عطر">
                </div>
                <div class="p-4">
                    <h3 class="font-medium text-gray-800 text-sm truncate mb-1">ست عطر و ادکلن زنانه</h3>
                    <div class="flex items-center gap-1 mb-2 text-amber-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <span class="text-xs text-gray-500">۴.۸ (۱۵۰)</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-900 font-bold text-sm block">۲,۲۰۰,۰۰۰ ت</span>
                        <button class="bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white p-2 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

{{-- فوتر --}}
<footer class="bg-gray-900 text-gray-400 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-2 md:grid-cols-4 gap-8">
        <div class="col-span-2 md:col-span-1">
            <p class="text-white font-bold text-xl mb-4">مارکت<span class="text-indigo-500">پلاس</span></p>
            <p class="text-sm leading-6">فروشگاه اینترنتی جامع با ضمانت اصالت کالا و بهترین قیمت بازار.</p>
        </div>
        <div>
            <h4 class="text-white font-bold mb-4 text-sm">با ما</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="#" class="hover:text-white transition">اتاق خبر</a></li>
                <li><a href="#" class="hover:text-white transition">فروش در مارکت‌پلاس</a></li>
                <li><a href="#" class="hover:text-white transition">فرصت‌های شغلی</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white font-bold mb-4 text-sm">خدمات مشتریان</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="#" class="hover:text-white transition">تماس با ما</a></li>
                <li><a href="#" class="hover:text-white transition">رویه‌های ارسال</a></li>
                <li><a href="#" class="hover:text-white transition">پیگیری سفارش</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white font-bold mb-4 text-sm">نمادهای اعتماد</h4>
            <div class="flex flex-col gap-2">
                <div class="bg-white w-24 h-24 rounded-lg"></div> {{-- جایگاه نماد اعتماد الکترونیک --}}
            </div>
        </div>
    </div>
    <div class="border-t border-gray-800 py-4 text-center text-xs">
        © ۲۰۲۳ تمامی حقوق برای مارکت‌پلاس محفوظ است. طراحی شده با Laravel و Tailwind.
    </div>
</footer>

</body>
</html>
