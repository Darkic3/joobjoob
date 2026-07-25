@extends('layouts.admin.master')

@section('title', "تصاویر خبر: {$news->title}")

@section('content')

    <div class="container-fluid mt-4">

        <div class="card mb-4">
            <div class="card-header">آپلود تصاویر</div>
            <div class="card-body">
                <form action="{{ route('admin.news-images.store', $news) }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @include('components.multi-image-cropper', [
                        'name' => 'images[]',
                        'label' => 'انتخاب تصاویر گالری',
                        'aspectWidth' => 1200,
                        'aspectHeight' => 840,

                    ])

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success px-5">آپلود</button>
                    </div>
                </form>

            </div>
        </div>
        <div class="row">
            @foreach($images as $image)
                <div class="col-md-3">
                    <div class="card">
                        <img src="{{ $image->image_file }}" style="height:200px;object-fit:cover" class="card-img-top">
                        <div class="card-body text-center">
                            @if($news->image === $image->image)
                                <span class="badge bg-warning">تصویر اصلی</span>
                            @else
                                <form action="{{ route('admin.news-images.thumbnail', [$news, $image]) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-primary btn-sm">انتخاب به عنوان اصلی</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.news-images.destroy', [$news, $image]) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">حذف</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
