@props(['headers', 'emptyMessage' => __('admin.common.no_items')])

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-start">
            <thead class="bg-gray-50/50 dark:bg-gray-700/30 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
            <tr>
                @foreach($headers as $header)
                    <th class="px-6 py-4 {{ $header['class'] ?? '' }}">{{ $header['label'] }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($slot->toHtml() as $row)
                {{ $row }}
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-sm">{{ $emptyMessage }}</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
