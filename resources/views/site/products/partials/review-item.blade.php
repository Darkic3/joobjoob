{{-- Recursive partial: رندر یک نظر + پاسخ‌های تو در تو --}}
@props(['review', 'depth' => 0])

@php
    $isReply = $depth > 0;
@endphp

<div class="review-card {{ $isReply ? 'review-card--reply' : '' }}" style="--depth: {{ $depth }};">
    <div class="review-card__head">
        <div class="review-card__user">
            <span class="review-card__avatar {{ $isReply ? 'review-card__avatar--sm' : '' }}">
                {{ mb_substr($review->display_name, 0, 1) }}
            </span>
            <div>
                <p class="review-card__name">
                    {{ $review->display_name }}
                    @if($isReply)
                        <span class="review-card__reply-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="10" height="10"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                            {{ __('messages.reply') }}
                        </span>
                    @endif
                </p>
                <p class="review-card__date">{{ show_date($review->created_at, 'd F Y') }}</p>
            </div>
        </div>
        @if(!$isReply)
            <div class="review-card__rating">
                @for($i = 1; $i <= 5; $i++)
                    <svg viewBox="0 0 24 24" fill="currentColor" class="review-card__star" style="color: {{ $i <= $review->rating ? 'var(--gold)' : 'var(--shadow-dark)' }};"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                @endfor
            </div>
        @endif
    </div>
    <p class="review-card__body">{{ $review->comment }}</p>
    <div class="review-card__actions">
        @if($depth < 3)
            <button type="button" class="review-reply-btn" onclick="openReplyModal({{ $review->id }}, '{{ addslashes($review->display_name) }}')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                {{ __('messages.reply') }}
            </button>
        @endif
    </div>

    {{-- پاسخ‌های تو در تو --}}
    @if(isset($review->replies) && $review->replies->isNotEmpty() && $depth < 3)
        <div class="review-replies" style="--depth: {{ $depth + 1 }};">
            @foreach($review->replies as $reply)
                @include('site.products.partials.review-item', ['review' => $reply, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
