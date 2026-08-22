@props(['url' => url()->current(), 'title' => ''])

@php
    $encodedUrl   = urlencode($url);
    $encodedTitle = urlencode($title);
    $plainUrl     = $url;
@endphp

<div class="share-section">
    <div class="share-pills">

        {{-- ─── تلگرام ─── --}}
        <a href="https://t.me/share/url?url={{ $encodedUrl }}&text={{ $encodedTitle }}"
           target="_blank" rel="noopener noreferrer"
           class="share-pill" data-platform="telegram"
           title="{{ __('messages.share_telegram') }}"
           aria-label="{{ __('messages.share_telegram') }}">
            <span class="share-pill__icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            </span>
            <span class="share-pill__label">تلگرام</span>
        </a>

        {{-- ─── واتساپ ─── --}}
        <a href="https://wa.me/?text={{ $encodedTitle }}%20{{ $encodedUrl }}"
           target="_blank" rel="noopener noreferrer"
           class="share-pill" data-platform="whatsapp"
           title="{{ __('messages.share_whatsapp') }}"
           aria-label="{{ __('messages.share_whatsapp') }}">
            <span class="share-pill__icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </span>
            <span class="share-pill__label">واتساپ</span>
        </a>



        {{-- ─── دکمه بیشتر ─── --}}
        <button type="button"
                class="share-pill share-pill--toggle"
                onclick="toggleShareOverflow(this)"
                data-text-more="{{ __('messages.share_more') }}"
                data-text-less="{{ __('messages.share_less') }}">
            <span class="share-pill__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                </svg>
            </span>
            <span class="share-pill__label">{{ __('messages.share_more') }}</span>
        </button>
    </div>

    {{-- ─── ردیف دوم: آیتم‌های مخفی ─── --}}
    <div class="share-overflow hidden">

        {{-- بله --}}
        <a href="https://bale.ai/messenger/share/url?url={{ $encodedUrl }}"
           target="_blank" rel="noopener noreferrer"
           onclick="event.preventDefault(); window.location.href='bale://msg?url={{ $encodedUrl }}&text={{ $encodedTitle }}'; setTimeout(function(){ window.open('https://bale.ai','_blank'); }, 800);"
           class="share-pill" data-platform="bale"
           title="{{ __('messages.share_bale') }}"
           aria-label="{{ __('messages.share_bale') }}">
            <span class="share-pill__icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.83 7.76L12.7 2.13c-.33-.25-.8-.25-1.13 0L5.43 7.76c-.38.29-.48.83-.21 1.24.13.2.33.35.56.41l1.73.46 1.01 3.68c.05.17.2.31.38.36.05.01.1.02.15.02.14 0 .27-.06.37-.17l2.22-2.47 2.44 1.83c.21.16.48.16.69 0l4.77-3.82c.35-.28.44-.78.21-1.18-.12-.2-.3-.35-.52-.41l-1.53-.37-.94-3.42c-.04-.15-.17-.27-.33-.32a.65.65 0 0 0-.43.06zM12 13.5l-2.13-1.6 6.13-5.63-1.73-.46L12 13.5zm-1.06 3.1l-2.22 2.47 4.23-3.18-2.01 1.51v-.8z"/></svg>
            </span>
            <span class="share-pill__label">بله</span>
        </a>

        {{-- ایتا --}}
        <a href="https://eitaa.com/share/url?url={{ $encodedUrl }}"
           target="_blank" rel="noopener noreferrer"
           onclick="event.preventDefault(); window.location.href='eitaa://share?url={{ $encodedUrl }}&text={{ $encodedTitle }}'; setTimeout(function(){ window.open('https://eitaa.com','_blank'); }, 800);"
           class="share-pill" data-platform="eitaa"
           title="{{ __('messages.share_eitaa') }}"
           aria-label="{{ __('messages.share_eitaa') }}">
            <span class="share-pill__icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.03-1.99 1.26-5.6 3.71-.53.37-1.01.55-1.44.54-.47-.01-1.38-.27-2.05-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 3.98-1.73 6.63-2.88 7.96-3.44 3.78-1.59 4.57-1.87 5.08-1.88.11 0 .37.03.54.17.14.12.18.28.2.47-.01.06.01.24 0 .37z"/></svg>
            </span>
            <span class="share-pill__label">ایتا</span>
        </a>

        {{-- استوری اینستاگرام --}}
        <a href="https://www.instagram.com/"
           target="_blank" rel="noopener noreferrer"
           onclick="event.preventDefault(); window.location.href='instagram://share?text={{ $encodedTitle }}&url={{ $encodedUrl }}'; setTimeout(function(){ window.location.href='https://www.instagram.com/'; }, 800);"
           class="share-pill" data-platform="instagram-story"
           title="{{ __('messages.share_ig_story') }}"
           aria-label="{{ __('messages.share_ig_story') }}">
            <span class="share-pill__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/><path d="M12 7v10M12 12l3-3M12 12l-3-3" opacity=".5"/></svg>
            </span>
            <span class="share-pill__label">استوری</span>
        </a>

        {{-- دایرکت اینستاگرام --}}
        <a href="https://www.instagram.com/direct/new/?text={{ $encodedTitle }}%20{{ $encodedUrl }}"
           target="_blank" rel="noopener noreferrer"
           class="share-pill" data-platform="instagram-dm"
           title="{{ __('messages.share_ig_dm') }}"
           aria-label="{{ __('messages.share_ig_dm') }}">
            <span class="share-pill__icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </span>
            <span class="share-pill__label">دایرکت</span>
        </a>


        {{-- ─── اشتراک‌گذاری (موبایل: Web Share API — دسکتاپ: کپی لینک) ─── --}}
        <button type="button"
                class="share-pill share-pill--native"
                title="{{ __('messages.share_native') }}"
                aria-label="{{ __('messages.share_native') }}"
                onclick="shareNative(this, {{ json_encode($plainUrl) }}, {{ json_encode($title) }})">
            <span class="share-pill__icon share-icon-share">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
            </span>
            <span class="share-pill__icon share-icon-check hidden">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </span>
            <span class="share-pill__label"
                  data-share-text="{{ __('messages.share_all') }}"
                  data-copy-text="{{ __('messages.share_copy') }}"
                  data-copied-text="{{ __('messages.share_copied') }}">{{ __('messages.share_all') }}</span>
        </button>

        {{-- کپی لینک --}}
        <button type="button"
                class="share-pill share-pill--copy"
                title="{{ __('messages.share_copy') }}"
                aria-label="{{ __('messages.share_copy') }}"
                onclick="copyShareLink(this, {{ json_encode($plainUrl) }})">
            <span class="share-pill__icon share-icon-copy">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
            </span>
            <span class="share-pill__icon share-icon-check hidden">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </span>
            <span class="share-pill__label"
                  data-copy-text="{{ __('messages.share_copy') }}"
                  data-copied-text="{{ __('messages.share_copied') }}">{{ __('messages.share_copy') }}</span>
        </button>
    </div>
</div>

<script>
/* ── Web Share API / Copy fallback ── */
function shareNative(btn, url, title) {
    if (navigator.share) {
        navigator.share({ title: title, text: title, url: url }).catch(function() {});
    } else {
        copyShareLink(btn, url);
    }
}

/* ── Toggle overflow ── */
function toggleShareOverflow(toggleBtn) {
    var section = toggleBtn.closest('.share-section');
    var overflow = section.querySelector('.share-overflow');
    var expanded = overflow.classList.toggle('hidden');
    toggleBtn.querySelector('.share-pill__label').textContent =
        expanded ? toggleBtn.dataset.textLess : toggleBtn.dataset.textMore;
    if (!expanded) return;
    /* Stagger animation on reveal */
    overflow.querySelectorAll('.share-pill').forEach(function(pill, i) {
        pill.style.animation = 'none';
        pill.offsetHeight; /* reflow */
        pill.style.animation = 'sharePop 0.3s ' + (i * 0.05) + 's both';
    });
}

/* ── Copy link ── */
function copyShareLink(btn, url) {
    var label = btn.querySelector('.share-pill__label');
    var copyIcon = btn.querySelector('.share-icon-copy');
    var checkIcon = btn.querySelector('.share-icon-check');
    function showCopied() {
        if (copyIcon) copyIcon.classList.add('hidden');
        if (checkIcon) checkIcon.classList.remove('hidden');
        btn.classList.add('share-pill--copied');
        if (label) label.textContent = label.dataset.copiedText;
        setTimeout(function() {
            if (copyIcon) copyIcon.classList.remove('hidden');
            if (checkIcon) checkIcon.classList.add('hidden');
            btn.classList.remove('share-pill--copied');
            if (label) label.textContent = label.dataset.copyText;
        }, 2200);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(showCopied).catch(function() { fallbackCopy(url, showCopied); });
    } else {
        fallbackCopy(url, showCopied);
    }
    function fallbackCopy(text, cb) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.cssText = 'position:fixed;opacity:0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        cb();
    }
}
</script>
