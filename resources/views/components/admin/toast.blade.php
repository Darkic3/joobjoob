<div
    x-data="{
        messages: [],
        init() {
            var flash = {{ json_encode(session('flash')) }};
            if (flash) {
                this.messages.push(flash);
            }
            @if(session('success'))
                this.messages.push({ type: 'success', message: {{ json_encode(session('success')) }} });
            @endif
            @if(session('error'))
                this.messages.push({ type: 'error', message: {{ json_encode(session('error')) }} });
            @endif
            @if(session('warning'))
                this.messages.push({ type: 'warning', message: {{ json_encode(session('warning')) }} });
            @endif
            @if(session('info'))
                this.messages.push({ type: 'info', message: {{ json_encode(session('info')) }} });
            @endif

            this.messages.forEach(function (msg, i) {
                setTimeout(function () { this.messages.splice(i, 1); }.bind(this), 5000);
            }.bind(this));
        },
        remove(index) { this.messages.splice(index, 1); }
    }"
    class="fixed top-6 left-6 z-[9999] max-w-sm w-full space-y-3">

    <template x-for="(msg, index) in messages" :key="index">
        <div x-show="true"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             :class="{
                 'bg-emerald-50 border-emerald-200 text-emerald-700': msg.type === 'success',
                 'bg-red-50 border-red-200 text-red-700': msg.type === 'error',
                 'bg-amber-50 border-amber-200 text-amber-700': msg.type === 'warning',
                 'bg-blue-50 border-blue-200 text-blue-700': msg.type === 'info'
             }"
             class="px-5 py-4 rounded-xl shadow-lg flex items-center gap-3 border">

            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                 :class="{
                     'bg-emerald-100': msg.type === 'success',
                     'bg-red-100': msg.type === 'error',
                     'bg-amber-100': msg.type === 'warning',
                     'bg-blue-100': msg.type === 'info'
                 }">
                <template x-if="msg.type === 'success'">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="msg.type === 'error'">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </template>
                <template x-if="msg.type === 'warning'">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </template>
                <template x-if="msg.type === 'info'">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
            </div>

            <p class="text-sm font-medium flex-1" x-text="msg.message"></p>

            <button @click="remove(index)" class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity"
                    :class="{
                        'text-emerald-500 hover:text-emerald-700': msg.type === 'success',
                        'text-red-500 hover:text-red-700': msg.type === 'error',
                        'text-amber-500 hover:text-amber-700': msg.type === 'warning',
                        'text-blue-500 hover:text-blue-700': msg.type === 'info'
                    }">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
