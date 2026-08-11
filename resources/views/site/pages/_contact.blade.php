<div class="grid lg:grid-cols-5 gap-6 mt-10">

    {{-- راه‌های ارتباطی --}}
    <section class="lg:col-span-2 space-y-4 reveal-on-scroll">
        <h2 class="page-section-title !mb-0">{{ $data['info_title'] }}</h2>
        <p class="text-sm leading-relaxed" style="color: var(--text-secondary);">{{ $data['info_desc'] }}</p>

        @foreach($data['info'] as $item)
            @if(!empty($item['value']))
                <div class="neu-card p-5 flex items-center gap-4">
                    <span class="page-contact-icon" aria-hidden="true">
                        @if($item['icon'] === 'phone')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                        @elseif($item['icon'] === 'email')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                        @else
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold mb-0.5" style="color: var(--text-secondary);">{{ $item['label'] }}</div>
                        <div class="text-sm font-bold break-words" style="color: var(--text-primary);" @if($item['dir']) dir="{{ $item['dir'] }}" @endif>
                            {{ $item['value'] }}
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </section>

    {{-- فرم تماس --}}
    <section class="lg:col-span-3 reveal-on-scroll">
        <div class="neu-flat rounded-3xl p-6 md:p-8">
            <h2 class="page-section-title !mb-1">{{ $data['form_title'] }}</h2>
            <p class="text-sm mb-6" style="color: var(--text-secondary);">{{ $data['form_desc'] }}</p>

            <form action="{{ route('site.contact.send') }}" method="POST" class="form-grid">
                @csrf

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-field">
                        <label for="contact-name" class="form-label">{{ $data['name'] }}</label>
                        <input id="contact-name" type="text" name="name" value="{{ old('name') }}" required maxlength="120"
                               placeholder="{{ $data['name_ph'] }}" class="neu-input" autocomplete="name">
                        @error('name')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-field">
                        <label for="contact-email" class="form-label">{{ $data['email'] }}</label>
                        <input id="contact-email" type="email" name="email" value="{{ old('email') }}" required maxlength="190"
                               placeholder="{{ $data['email_ph'] }}" class="neu-input" autocomplete="email" dir="ltr">
                        @error('email')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-field">
                        <label for="contact-phone" class="form-label">{{ $data['phone'] }}</label>
                        <input id="contact-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="40"
                               placeholder="{{ $data['phone_ph'] }}" class="neu-input" autocomplete="tel" dir="ltr">
                        @error('phone')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-field">
                        <label for="contact-department" class="form-label">{{ $data['department'] }}</label>
                        <select id="contact-department" name="department" class="neu-input" style="appearance: none;">
                            <option value="">{{ $data['department_ph'] }}</option>
                            @foreach($data['departments'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('department') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('department')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-field">
                    <label for="contact-message" class="form-label">{{ $data['message'] }}</label>
                    <textarea id="contact-message" name="message" rows="5" required minlength="10" maxlength="2000"
                              placeholder="{{ $data['message_ph'] }}" class="neu-input resize-y">{{ old('message') }}</textarea>
                    @error('message')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="neu-btn neu-btn-primary mt-1 w-full sm:w-auto">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg>
                    <span>{{ $data['submit'] }}</span>
                </button>
            </form>
        </div>
    </section>
</div>
