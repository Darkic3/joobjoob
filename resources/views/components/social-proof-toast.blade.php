{{--
    toast اثبات اجتماعی چرخشی — "فلانی همین الان این محصول را خرید".
    Props: items — آرایه‌ای از [نام, پیام]؛ در پروژه واقعی از جدول orders آخرین چند خرید را بگیرید.
--}}
@props(['items' => [
    ['علی از تهران', '۲ دقیقه پیش «کتانی رانینگ پرو» را خرید'],
    ['نگار از اصفهان', 'همین الان «ساعت هوشمند اسپرت» را خرید'],
    ['حسین از شیراز', '۵ دقیقه پیش «هدفون بی‌سیم» را خرید'],
]])

{{-- z-[35]: زیر منوی موبایل (۴۵) و نوار پایین (۴۰) تا هنگام باز بودن منو پشت آن محو شود --}}
<div id="social-toast" class="fixed bottom-24 start-5 z-[35] hidden toast-enter lg:bottom-5" data-items='{!! htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8') !!}'>
    <div class="neu-flat-sm px-4 py-3 flex items-center gap-3 max-w-xs">
        <span class="neu-icon-circle w-9 h-9 grid place-items-center text-sm shrink-0">🛍️</span>
        <div class="text-xs">
            <p class="font-bold" id="toast-name"></p>
            <p style="color:var(--text-secondary);" id="toast-msg"></p>
        </div>
        <button type="button" onclick="document.getElementById('social-toast').classList.add('hidden')"
                class="text-sm shrink-0" style="color:var(--text-secondary);">✕</button>
    </div>
</div>
