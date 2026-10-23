<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاکتور سفارش #{{ $order->id }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            background: #fff;
            line-height: 1.5;
            direction: rtl;
        }

        /* ── print ── */
        @media print {
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }

        /* ── buttons ── */
        .actions {
            position: fixed; top: 16px; left: 50%; transform: translateX(-50%);
            display: flex; gap: 8px; z-index: 100;
        }
        .actions button {
            padding: 8px 20px; border: none; border-radius: 8px;
            font-size: 13px; font-weight: 600; cursor: pointer;
            transition: all .2s;
        }
        .btn-print { background: #4361ee; color: #fff; }
        .btn-print:hover { background: #3a56d4; }
        .btn-close { background: #e5e7eb; color: #374151; }
        .btn-close:hover { background: #d1d5db; }

        /* ── invoice card ── */
        .invoice {
            max-width: 700px;
            margin: 60px auto 20px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        /* ── header ── */
        .inv-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding: 20px 24px 16px;
            border-bottom: 2px solid #1a1a2e;
        }
        .inv-brand { font-size: 20px; font-weight: 800; color: #1a1a2e; letter-spacing: -0.5px; }
        .inv-brand-sub { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .inv-title {
            text-align: left;
        }
        .inv-title h1 {
            font-size: 16px; font-weight: 700; color: #1a1a2e;
            background: #f3f4f6; display: inline-block;
            padding: 4px 14px; border-radius: 4px;
        }
        .inv-title p { font-size: 10px; color: #6b7280; margin-top: 4px; }

        /* ── meta row ── */
        .inv-meta {
            display: grid; grid-template-columns: 1fr 1fr 1fr;
            gap: 1px; background: #e5e7eb;
            margin: 0;
        }
        .inv-meta-item {
            background: #f9fafb;
            padding: 10px 16px;
        }
        .inv-meta-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .inv-meta-value { font-size: 12px; font-weight: 700; color: #1a1a2e; }

        /* ── parties ── */
        .inv-parties {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 1px; background: #e5e7eb;
        }
        .inv-party {
            background: #fff;
            padding: 14px 20px;
        }
        .inv-party-title {
            font-size: 9px; font-weight: 700; color: #9ca3af;
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #f3f4f6;
        }
        .inv-party-name { font-size: 13px; font-weight: 700; color: #1a1a2e; margin-bottom: 3px; }
        .inv-party-detail { font-size: 10px; color: #6b7280; line-height: 1.6; }

        /* ── items table ── */
        .inv-items { padding: 0; }
        .inv-items table {
            width: 100%; border-collapse: collapse;
        }
        .inv-items thead th {
            background: #1a1a2e;
            color: #fff;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 12px;
            text-align: right;
        }
        .inv-items thead th:last-child,
        .inv-items thead th:nth-child(3),
        .inv-items thead th:nth-child(4) {
            text-align: left;
        }
        .inv-items tbody td {
            padding: 8px 12px;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }
        .inv-items tbody td:last-child,
        .inv-items tbody td:nth-child(3),
        .inv-items tbody td:nth-child(4) {
            text-align: left;
            font-weight: 600;
        }
        .inv-items tbody tr:hover { background: #f9fafb; }
        .inv-items .row-num {
            width: 28px; text-align: center;
            color: #9ca3af; font-weight: 600;
        }
        .inv-items .product-name { font-weight: 600; color: #1a1a2e; }
        .inv-items .product-variant { font-size: 9px; color: #9ca3af; }

        /* ── totals ── */
        .inv-totals {
            display: flex; justify-content: flex-start;
            padding: 16px 20px;
            border-top: 2px solid #e5e7eb;
        }
        .inv-totals-box {
            width: 280px;
        }
        .inv-total-row {
            display: flex; justify-content: space-between;
            padding: 4px 0;
            font-size: 11px;
            color: #6b7280;
        }
        .inv-total-row.discount { color: #059669; }
        .inv-total-row.grand {
            border-top: 2px solid #1a1a2e;
            margin-top: 6px; padding-top: 8px;
            font-size: 14px; font-weight: 800; color: #1a1a2e;
        }

        /* ── footer ── */
        .inv-footer {
            padding: 12px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex; justify-content: space-between;
            font-size: 9px; color: #9ca3af;
        }
        .inv-footer-note {
            padding: 10px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            font-size: 9px; color: #6b7280;
            text-align: center;
        }

        /* ── barcode placeholder ── */
        .inv-barcode {
            text-align: center;
            padding: 10px 20px;
            border-top: 1px dashed #e5e7eb;
        }
        .inv-barcode-line {
            display: inline-block;
            font-family: 'Courier New', monospace;
            font-size: 10px; letter-spacing: 2px;
            color: #1a1a2e;
            padding: 4px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            background: #f9fafb;
        }
    </style>
</head>
<body>

    {{-- دکمه‌های چاپ --}}
    <div class="actions no-print">
        <button onclick="window.print()" class="btn-print">🖨️ چاپ فاکتور</button>
        <button onclick="window.close()" class="btn-close">بستن</button>
    </div>

    <div class="invoice">

        {{-- هدر --}}
        <div class="inv-header">
            <div>
                <div class="inv-brand">{{ config('app.name', 'Joobjoob') }}</div>
                <div class="inv-brand-sub">فاکتور خرید</div>
            </div>
            <div class="inv-title">
                <h1>سفارش #{{ $order->id }}</h1>
                <p>{{ show_date($order->created_at, 'd F Y — H:i') }}</p>
            </div>
        </div>

        {{-- اطلاعات کلی --}}
        <div class="inv-meta">
            <div class="inv-meta-item">
                <div class="inv-meta-label">شماره سفارش</div>
                <div class="inv-meta-value">#{{ $order->id }}</div>
            </div>
            <div class="inv-meta-item">
                <div class="inv-meta-label">تاریخ ثبت</div>
                <div class="inv-meta-value">{{ show_date($order->created_at, 'd/m/Y') }}</div>
            </div>
            <div class="inv-meta-item">
                <div class="inv-meta-label">روش ارسال</div>
                <div class="inv-meta-value">{{ $order->shippingMethod?->title ?? '—' }}</div>
            </div>
        </div>

        {{-- فروشنده و خریدار --}}
        <div class="inv-parties">
            <div class="inv-party">
                <div class="inv-party-title">فروشنده</div>
                <div class="inv-party-name">{{ setting('site_name', config('app.name', 'Joobjoob')) }}</div>
                <div class="inv-party-detail">
                    @if(config('app.company_address'))
                        {{ config('app.company_address') }}<br>
                    @endif
                    @if(config('app.company_phone'))
                        تلفن: {{ config('app.company_phone') }}
                    @endif
                </div>
            </div>
            <div class="inv-party">
                <div class="inv-party-title">تحویل‌گیرنده</div>
                <div class="inv-party-name">{{ $order->receiptor_full_name ?? '—' }}</div>
                <div class="inv-party-detail">
                    @if($order->address)
                        {{ $order->address['state'] ?? '' }} — {{ $order->address['city'] ?? '' }}<br>
                        {{ $order->address['address'] ?? '' }}<br>
                        @if($order->address['postal_code'] ?? null)
                            کدپستی: <span dir="ltr">{{ $order->address['postal_code'] }}</span>
                        @endif
                        @if($order->address['receiptor_mobile'] ?? null)
                            | موبایل: <span dir="ltr">{{ $order->address['receiptor_mobile'] }}</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- اقلام سفارش --}}
        <div class="inv-items">
            <table>
                <thead>
                    <tr>
                        <th class="row-num">#</th>
                        <th>محصول</th>
                        <th>تعداد</th>
                        <th>قیمت واحد</th>
                        <th>جمع</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $index => $item)
                        <tr>
                            <td class="row-num">{{ $index + 1 }}</td>
                            <td>
                                <span class="product-name">{{ $item->product?->trans('name') ?? $item->product?->name ?? 'محصول حذف شده' }}</span>
                                @if($item->productVariant)
                                    <br><span class="product-variant">{{ $item->productVariant->label ?? '' }}</span>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price) }}</td>
                            <td>{{ number_format($item->price * $item->quantity) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; color:#9ca3af; padding:20px;">اقلام ثبت نشده</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- جمع مالی --}}
        <div class="inv-totals">
            <div class="inv-totals-box">
                <div class="inv-total-row">
                    <span>جمع کل محصولات</span>
                    <span>{{ number_format($order->total_price) }} تومان</span>
                </div>
                @if($order->discount_price > 0)
                    <div class="inv-total-row discount">
                        <span>تخفیف{{ $order->discount?->code ? ' (' . $order->discount->code . ')' : '' }}</span>
                        <span>-{{ number_format($order->discount_price) }} تومان</span>
                    </div>
                @endif
                <div class="inv-total-row">
                    <span>هزینه ارسال</span>
                    <span>
                        @if($order->shipping_price > 0)
                            {{ number_format($order->shipping_price) }} تومان
                        @elseif($order->freight_balance)
                            پس کرایه
                        @else
                            رایگان
                        @endif
                    </span>
                </div>
                <div class="inv-total-row grand">
                    <span>مبلغ قابل پرداخت</span>
                    <span>{{ number_format($order->payment_price) }} تومان</span>
                </div>
            </div>
        </div>

        {{-- بارکد / کد پیگیری --}}
        <div class="inv-barcode">
            <span class="inv-barcode-line">ORD-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>

        {{-- پیام --}}
        <div class="inv-footer-note">
            از خرید شما متشکریم 🙏
        </div>

        {{-- فوتر --}}
        <div class="inv-footer">
            <span>{{ config('app.name') }}</span>
            <span>چاپ: {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

</body>
</html>
