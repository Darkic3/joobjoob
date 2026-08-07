@extends('layouts.admin.app')

@section('tab-name', __('admin.hero_slides.tab'))

@section('content')
    <div class="space-y-6 animate-slide-up">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('admin.hero_slides.title') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.hero_slides.subtitle') }}</p>
            </div>
            @if(auth()->user()->canAccess('admin.hero-slides.create'))
                <a href="{{ route('admin.hero-slides.create') }}" class="btn-primary flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('admin.hero_slides.add_new') }}</span>
                </a>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-start">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-16">#</th>
                        <th class="px-6 py-4 w-32">{{ __('admin.hero_slides.image') }}</th>
                        <th class="px-6 py-4">{{ __('admin.common.title') }}</th>
                        <th class="px-6 py-4">{{ __('admin.hero_slides.badge') }}</th>
                        <th class="px-6 py-4">{{ __('admin.common.sort_order') }}</th>
                        <th class="px-6 py-4">{{ __('admin.common.status') }}</th>
                        <th class="px-6 py-4 text-end w-24">{{ __('admin.common.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($slides as $slide)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors group">
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                @if($slide->image)
                                    <img src="{{ asset(config('paths.upload', 'uploads') . '/hero-slides/' . $slide->image) }}"
                                         class="w-16 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-600" alt="Slide">
                                @else
                                    <div class="w-16 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 dark:text-gray-100 truncate max-w-[200px]" title="{{ $slide->trans('title') }}">
                                    {{ Str::limit($slide->trans('title'), 40) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400 border border-purple-100 dark:border-purple-800">
                                        {{ $slide->trans('badge') ?: __('admin.hero_slides.no_badge') }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-gray-600 dark:text-gray-300">
                                {{ $slide->sort_order }}
                            </td>
                            <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg {{ $slide->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800' }}">
                                        <span class="w-1.5 h-1.5 {{ $slide->is_active ? 'bg-emerald-500' : 'bg-red-500' }} rounded-full"></span>
                                        {{ $slide->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 text-end">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="{{ __('admin.common.edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="POST" class="delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all" title="{{ __('admin.common.delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <p class="text-gray-500 dark:text-gray-400">{{ __('admin.hero_slides.empty') }}</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($slides->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    {{ $slides->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
