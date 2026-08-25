@extends('layouts.admin.app')

@section('tab-name', 'مدیریت روش‌های ارسال')

@section('content')
    <div class="flex justify-content-between align-items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">🚚 مدیریت روش‌های ارسال</h2>
        <a href="{{ route('admin.shipping-methods.create') }}" class="btn btn-primary btn-sm">+ افزودن روش ارسال</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>عنوان</th>
                        <th>نوع</th>
                        <th>هزینه</th>
                        <th>سقف رایگان</th>
                        <th>زمان تخمینی</th>
                        <th>پس کرایه</th>
                        <th>شهرها</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($methods as $method)
                        <tr>
                            <td>{{ $method->id }}</td>
                            <td><strong>{{ $method->title }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $method->typeName['badge'] }}">{{ $method->typeName['title'] }}</span>
                            </td>
                            <td>
                                @if($method->type === 'with_weight')
                                    {{ number_format($method->price) }} تومان / ۱۰۰g
                                @elseif($method->price)
                                    {{ number_format($method->price) }} تومان
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($method->price_ceiling > 0)
                                    بالای {{ number_format($method->price_ceiling) }} تومان
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $method->estimated_days_text }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $method->freightBalanceName['badge'] }}">{{ $method->freightBalanceName['title'] }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $method->cities->count() }} شهر</span>
                            </td>
                            <td>
                                <form action="{{ route('admin.shipping-methods.toggle-status', $method->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $method->status ? 'btn-success' : 'btn-outline-secondary' }}">
                                        {{ $method->status ? 'فعال' : 'غیرفعال' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.shipping-methods.edit', $method->id) }}" class="btn btn-sm btn-outline-primary">ویرایش</a>
                                <form action="{{ route('admin.shipping-methods.destroy', $method->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('آیا از حذف این روش ارسال مطمئن هستید؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">هیچ روش ارسالی تعریف نشده است</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
