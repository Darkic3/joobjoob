@extends('layouts.admin.app')

@section('tab-name', __('admin.monitoring.error_detail'))

@section('content')
<div class="space-y-6 animate-slide-up">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.monitoring.error_detail') }} #{{ $error->id }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ $error->created_at->format('Y/m/d H:i:s') }}</p>
        </div>
        <a href="{{ route('admin.monitoring.errors') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-xl transition-all flex items-center gap-2">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('admin.monitoring.back_to_dashboard') }}
        </a>
    </div>

    {{-- Status & Level --}}
    <div class="flex flex-wrap items-center gap-3">
        <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-full
            {{ $error->level === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
            {{ $error->level === 'error' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : '' }}
            {{ $error->level === 'warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}">
            {{ __('admin.monitoring.level_' . $error->level) }}
        </span>
        @if($error->is_resolved)
            <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="w-4 h-4 ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('admin.monitoring.resolved') }}
                @if($error->resolved_note)
                    <span class="text-xs font-normal ms-1 opacity-75">({{ $error->resolved_note }})</span>
                @endif
            </span>
        @else
            <form action="{{ route('admin.monitoring.errors.resolve', $error->id) }}" method="POST" class="inline-flex flex-wrap items-center gap-2" x-data="{ showNote: false }">
                @csrf
                <input type="hidden" name="note" :value="showNote ? $refs.noteInput.value : ''">
                <button type="button" @click="showNote = !showNote" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-xl transition-all">{{ __('admin.monitoring.mark_resolved') }}</button>
                <div x-show="showNote" x-transition class="inline-flex items-center gap-2">
                    <input x-ref="noteInput" type="text" placeholder="{{ __('admin.monitoring.resolve_note_placeholder') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 w-64">
                    <button type="submit" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium rounded-xl transition-all">ذخیره</button>
                </div>
            </form>
        @endif
    </div>

    {{-- خلاصه خطا — کپی یک‌کلیکه برای بررسی / ارسال به AI --}}
    <x-admin.card :title="__('admin.monitoring.error_summary')" :subtitle="__('admin.monitoring.error_summary_subtitle')" :padding="false">
        <x-slot name="actions">
            <button type="button" id="copyErrorSummaryBtn"
                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded-xl transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span id="copyErrorSummaryLabel">{{ __('admin.monitoring.copy_summary') }}</span>
            </button>
        </x-slot>
        <div class="p-4">
            <pre id="errorSummaryPre" class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-xs text-gray-700 dark:text-gray-300 overflow-x-auto leading-relaxed text-left max-h-96 overflow-y-auto" dir="ltr">{{ $errorSummary ?? '' }}</pre>
        </div>
    </x-admin.card>

    {{-- Message --}}
    <x-admin.card :title="__('admin.monitoring.message')">
        <p class="text-base text-gray-800 dark:text-gray-200 leading-relaxed break-words" dir="auto">{{ $error->message }}</p>
    </x-admin.card>

    {{-- Exception Class --}}
    <x-admin.card :title="__('admin.monitoring.exception')">
        <p class="font-mono text-sm text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 rounded-xl px-4 py-3 break-all text-left" dir="ltr">{{ $error->exception_class }}</p>
    </x-admin.card>

    {{-- File & Line --}}
    <x-admin.card :title="__('admin.monitoring.file')">
        <div class="flex flex-wrap items-center gap-3 bg-gray-50 dark:bg-gray-900 rounded-xl px-4 py-3" dir="ltr">
            <p class="font-mono text-sm text-gray-800 dark:text-gray-200 break-all flex-1 min-w-0 text-left">{{ $error->file }}</p>
            @if($error->line)
                <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-bold font-mono">line {{ $error->line }}</span>
            @endif
        </div>
    </x-admin.card>

    {{-- Request Info --}}
    @if($error->url || $error->method || $error->ip)
        <x-admin.card :title="__('admin.monitoring.request_info')">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                @if($error->method)
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1.5">{{ __('admin.monitoring.method') }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold
                            {{ $error->method === 'GET' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' }}" dir="ltr">
                            {{ $error->method }}
                        </span>
                    </div>
                @endif
                @if($error->ip)
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1.5">{{ __('admin.monitoring.ip') }}</p>
                        <p class="text-sm text-gray-800 dark:text-gray-200 font-mono break-all" dir="ltr">{{ $error->ip }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-1.5">{{ __('admin.monitoring.user') }}</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $error->user?->name ?? __('admin.monitoring.no_user') }}</p>
                </div>
            </div>
            @if($error->url)
                <div class="mt-5">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-1.5">URL</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200 font-mono break-all bg-gray-50 dark:bg-gray-900 rounded-lg px-3 py-2 text-left" dir="ltr">{{ $error->url }}</p>
                </div>
            @endif
        </x-admin.card>
    @endif

    {{-- Stack Trace --}}
    <x-admin.card :title="__('admin.monitoring.trace')" :subtitle="count($traceFrames) ? trans('admin.monitoring.trace_frames', ['count' => count($traceFrames)]) : null" :padding="false">
        @if(!empty($traceFrames))
            <div class="divide-y divide-gray-100 dark:divide-gray-700" dir="ltr">
                @foreach($traceFrames as $frame)
                    <div class="flex items-start gap-3 px-4 sm:px-6 py-3 {{ $loop->first ? 'bg-orange-50/60 dark:bg-orange-900/10' : '' }}">
                        <span class="shrink-0 mt-0.5 inline-flex items-center justify-center min-w-[2.75rem] px-2 py-1 rounded-lg text-xs font-mono font-bold
                            {{ $loop->first ? 'bg-orange-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300' }}">
                            #{{ $frame['num'] ?? '•' }}
                        </span>
                        <div class="min-w-0 flex-1 space-y-1.5">
                            @if($frame['call'])
                                <p class="font-mono text-[13px] font-semibold text-indigo-600 dark:text-indigo-400 break-words text-left">{{ $frame['call'] }}</p>
                            @endif
                            @if($frame['file'])
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-mono text-xs text-gray-600 dark:text-gray-300 break-all min-w-0">{{ $frame['file'] }}</p>
                                    @if($frame['line'])
                                        <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-[11px] font-mono font-bold">:{{ $frame['line'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($traceRaw)
            <div class="p-4">
                <pre class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-xs text-gray-700 dark:text-gray-300 overflow-x-auto leading-relaxed text-left" dir="ltr">{{ $traceRaw }}</pre>
            </div>
        @else
            <p class="px-6 py-8 text-center text-sm text-gray-400 dark:text-gray-500">—</p>
        @endif
    </x-admin.card>

    {{-- Context --}}
    @if(filled($error->context) && (is_array($error->context) ? count($error->context) > 0 : true))
        <x-admin.card :title="__('admin.monitoring.context')" :padding="false">
            <div class="p-4">
                <pre class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-xs text-gray-700 dark:text-gray-300 overflow-x-auto leading-relaxed text-left" dir="ltr">{{ json_encode($error->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) }}</pre>
            </div>
        </x-admin.card>
    @endif

</div>

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('copyErrorSummaryBtn');
    var label = document.getElementById('copyErrorSummaryLabel');
    var pre = document.getElementById('errorSummaryPre');
    if (!btn || !pre) return;

    var defaultLabel = label ? label.textContent : '';
    var doneLabel = @json(__('admin.monitoring.copied'));
    var failLabel = @json(__('admin.monitoring.copy_failed'));

    function flash(text) {
        if (!label) return;
        label.textContent = text;
        setTimeout(function () { label.textContent = defaultLabel; }, 2000);
    }

    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        var ok = false;
        try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
        document.body.removeChild(ta);
        return ok;
    }

    btn.addEventListener('click', function () {
        var text = pre.innerText || pre.textContent || '';
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(
                function () { flash(doneLabel); },
                function () { flash(fallbackCopy(text) ? doneLabel : failLabel); }
            );
        } else {
            flash(fallbackCopy(text) ? doneLabel : failLabel);
        }
    });
})();
</script>
@endpush
@endsection
