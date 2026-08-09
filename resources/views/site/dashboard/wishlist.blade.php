@extends('layouts.app')
@section('title', __('messages.wishlist'))
@section('content')
<h1 class="text-xl font-bold mb-6">{{ __('messages.wishlist') }}</h1>
<div class="neu-card text-center py-16" style="color: var(--text-secondary);">
    {{-- TODO: مدل Wishlist و ارتباط many-to-many با کاربر را اضافه کنید --}}
</div>
@endsection
