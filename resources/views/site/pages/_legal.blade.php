{{-- قوانین / حریم خصوصی — متن تایپوگرافیک --}}
<section class="page-section reveal-on-scroll">
    <div class="max-w-3xl">
        @foreach($data['sections'] ?? [] as $section)
            <div class="legal-section">
                <h2 class="page-section-title">{{ $section['title'] }}</h2>
                <div class="page-prose">
                    @foreach($section['body'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
