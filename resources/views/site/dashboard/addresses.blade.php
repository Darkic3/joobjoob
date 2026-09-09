@extends('layouts.site.app')
@section('title', __('messages.addresses'))
@section('content')
<div class="grid md:grid-cols-4 gap-6">
    <div class="md:col-span-1">
        <div class="neu-card p-4 h-fit sticky top-24">
            @include('site.dashboard._sidebar')
        </div>
    </div>
    <div class="md:col-span-3 space-y-5" x-data="addressManager()">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl grid place-items-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h2 class="font-bold" style="color: var(--text-primary);">{{ __("messages.addresses") }}</h2>
            </div>
            <button @click="showForm = true; resetForm()" class="neu-btn neu-btn-primary px-4 py-2 text-xs font-bold rounded-full">+ افزودن آدرس</button>
        </div>
        @if(session("success"))
        <div class="neu-card p-4" style="border: 1px solid color-mix(in srgb, var(--success) 25%, transparent);">
            <span class="text-sm font-semibold" style="color: var(--success);">{{ session("success") }}</span>
        </div>
        @endif
        {{-- خطاهای ولیدیشن سمت سرور — پنل فرم باز می‌ماند --}}
        @if($errors->any())
        <div class="neu-card p-4" style="border: 1px solid color-mix(in srgb, var(--danger) 25%, transparent);">
            <p class="text-xs font-bold mb-1.5" style="color: var(--danger);">لطفاً موارد زیر را اصلاح کنید:</p>
            <ul class="list-disc pe-4 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li class="text-xs" style="color: var(--danger);">{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div x-show="showForm" x-transition x-cloak class="neu-card" @if($errors->any()) style="display: block;" x-init="showForm = true" @endif>
            <h3 class="text-sm font-bold mb-4" style="color: var(--text-primary);" x-text="editingId ? 'ویرایش آدرس' : 'آدرس جدید'"></h3>
            <form :action="editingId ? '{{ url("account/addresses") }}/' + editingId : '{{ route("dashboard.addresses.create") }}'" method="POST" class="space-y-3">
                @csrf
                <template x-if="editingId"><input type="hidden" name="_method" value="PUT"></template>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div><label class="text-[11px] font-bold mb-1 block" style="color: var(--text-secondary);">نام تحویل‌گیرنده *</label><input type="text" name="receiptor_full_name" value="{{ old('receiptor_full_name') }}" x-model="form.name" class="neu-input py-2.5 text-sm" required maxlength="120"></div>
                    <div><label class="text-[11px] font-bold mb-1 block" style="color: var(--text-secondary);">موبایل *</label><input type="tel" name="receiptor_mobile" value="{{ old('receiptor_mobile') }}" x-model="form.mobile" class="neu-input py-2.5 text-sm" dir="ltr" inputmode="tel" required maxlength="11" placeholder="09123456789"></div>
                    <div><label class="text-[11px] font-bold mb-1 block" style="color: var(--text-secondary);">استان *</label><select name="state_id" x-model="form.state_id" @change="loadCities(form.state_id)" class="neu-input py-2.5 text-sm" required><option value="">انتخاب استان</option>@foreach($states as $state)<option value="{{ $state->id }}" @if((string) old('state_id') === (string) $state->id) selected @endif>{{ $state->name }}</option>@endforeach</select></div>
                    <div><label class="text-[11px] font-bold mb-1 block" style="color: var(--text-secondary);">شهر *</label><select name="city_id" x-model="form.city_id" class="neu-input py-2.5 text-sm" required><option value="">انتخاب شهر</option><template x-for="city in cities" :key="city.id"><option :value="city.id" x-text="city.name"></option></template></select></div>
                    <div class="sm:col-span-2"><label class="text-[11px] font-bold mb-1 block" style="color: var(--text-secondary);">آدرس کامل *</label><textarea name="address" x-model="form.address" class="neu-input py-2.5 text-sm" rows="2" required maxlength="1000">{{ old('address') }}</textarea></div>
                    <div><label class="text-[11px] font-bold mb-1 block" style="color: var(--text-secondary);">کدپستی *</label><input type="tel" name="postal_code" value="{{ old('postal_code') }}" x-model="form.postal_code" class="neu-input py-2.5 text-sm" dir="ltr" inputmode="numeric" required maxlength="10"></div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="neu-btn neu-btn-primary px-5 py-2.5 text-xs font-bold rounded-full">ذخیره</button>
                    <button type="button" @click="showForm = false; resetForm()" class="neu-btn px-5 py-2.5 text-xs font-bold rounded-full">انصراف</button>
                </div>
            </form>
        </div>        @if($addresses->isEmpty())
        <div class="neu-card text-center py-12">
            <div class="w-16 h-16 rounded-3xl grid place-items-center mx-auto mb-4" style="background: color-mix(in srgb, var(--text-secondary) 8%, transparent);"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
            <p class="text-sm font-bold mb-3" style="color: var(--text-primary);">هنوز آدرسی ذخیره نکرده‌اید</p>
            <button @click="showForm = true; resetForm()" class="neu-btn neu-btn-primary px-5 py-2.5 text-xs font-bold rounded-full">افزودن آدرس</button>
        </div>
        @else
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($addresses as $address)
            <div class="neu-card">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl grid place-items-center shrink-0" style="background: color-mix(in srgb, var(--accent) 10%, transparent);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
                    <div class="flex-1 min-w-0"><p class="text-sm font-bold truncate" style="color: var(--text-primary);">{{ $address->receiptor_full_name }}</p><p class="text-[11px]" style="color: var(--text-secondary);">{{ $address->state?->name }} — {{ $address->city?->name }}</p></div>
                </div>
                <p class="text-xs leading-relaxed mb-3" style="color: var(--text-secondary);">{{ $address->receiptor_full_name }}</p>
                <div class="flex items-center gap-3 text-[11px]" style="color: var(--text-secondary);"><span dir="ltr">{{ $address->receiptor_full_name }}</span><span>|</span><span dir="ltr">{{ $address->receiptor_full_name }}</span></div>
                <div class="flex items-center gap-2 mt-3 pt-3" style="border-top: 1px solid color-mix(in srgb, var(--text-primary) 6%, transparent);">
                    <button @click="editAddress({{ json_encode($address) }})" class="text-[11px] font-bold px-3 py-1.5 rounded-lg" style="background: color-mix(in srgb, var(--accent) 10%, transparent); color: var(--accent);">ویرایش</button>
                    <form action="{{ route('dashboard.addresses.delete', $address->id) }}" method="POST" onsubmit="return confirm('آیا مطمئن هستید؟')">@csrf @method('DELETE')<button type="submit" class="text-[11px] font-bold px-3 py-1.5 rounded-lg" style="background: color-mix(in srgb, var(--danger) 10%, transparent); color: var(--danger);">حذف</button></form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@push("scripts")
<script>
function addressManager() {
    return {
        showForm: false, editingId: null, cities: [],
        form: { name:"", mobile:"", state_id:"", city_id:"", address:"", postal_code:"" },
        resetForm() { this.editingId = null; this.form = { name:"", mobile:"", state_id:"", city_id:"", address:"", postal_code:"" }; this.cities = []; },
        editAddress(a) { this.editingId = a.id; this.form = { name:a.receiptor_full_name, mobile:a.receiptor_mobile, state_id:a.state_id, city_id:a.city_id, address:a.address, postal_code:a.postal_code }; this.loadCities(a.state_id); this.showForm = true; },
        async loadCities(s) { if(!s){this.cities=[];return;} const r = await fetch('{{ route("dashboard.addresses.cities") }}?state_id='+s); const d = await r.json(); this.cities = d.cities||[]; }
    };
}

// ── بعد از خطای ولیدیشن سمت سرور: پنل را باز نگه دار + شهر قبلی را بازیابی کن + toast قرمز ──
@if($errors->any())
document.addEventListener('DOMContentLoaded', function () {
    // toast قرمز برای هر خطا
    if (typeof showToast === 'function') {
        @foreach($errors->all() as $i => $err)
            setTimeout(function() { showToast({{ json_encode($err) }}, 'error'); }, {{ $i * 350 }});
        @endforeach
    }

    // اگر استان قبلی انتخاب بوده، شهرها را بگیر و شهر قبلی را preselect کن
    var stateSelect = document.querySelector('select[name="state_id"]');
    var oldState = stateSelect ? stateSelect.value : '';
    if (oldState) {
        var manager = document.querySelector('[x-data="addressManager()"]');
        if (manager) {
            var scope = Alpine.$data(manager);
            fetch('{{ route("dashboard.addresses.cities") }}?state_id=' + oldState)
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    scope.cities = d.cities || [];
                    var oldCity = '{{ old("city_id") }}';
                    if (oldCity) scope.form.city_id = oldCity;
                });
        }
    }
});
@endif
</script>
@endpush
@endsection
