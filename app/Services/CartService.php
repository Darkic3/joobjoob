<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Support\Collection;

/**
 * سرویس سبد خرید — ترکیب Session (مهمان) + دیتابیس (کاربر لاگین‌شده).
 */
class CartService
{
    protected string $sessionKey = 'cart';

    /**
     * آیا کاربر لاگین است؟
     */
    private function isAuth(): bool
    {
        return auth()->check();
    }

    /**
     * دریافت محتوای سبد ( Collection از ردیف‌ها )
     */
    public function content(): Collection
    {
        if ($this->isAuth()) {
            return $this->dbContent();
        }

        return $this->sessionContent();
    }

    // ─── دیتابیس (کاربر لاگین) ────────────────────────────────────────

    private function dbContent(): Collection
    {
        $items = Cart::where('user_id', auth()->id())->get();

        // قیمت/موجودی سبد تا قبل از ثبت سفارش همیشه با قیمت روز سایت همگام است
        $this->refreshDbPrices($items);

        // بروزرسانی موجودی لحظه‌ای
        $stockService = app(StockService::class);
        $items = $stockService->enrichCartItemsWithStockStatus($items);

        return $items->mapWithKeys(fn ($item) => [$item->id => [
            'rowId'          => $item->id,
            'product_id'     => $item->product_id,
            'variant_id'     => $item->variant_id,
            'variant_label'  => $item->variant_label,
            'name'           => $item->name,
            'slug'           => $item->slug,
            'price'          => $item->price,
            'original_price' => $item->original_price,
            'qty'            => $item->qty,
            'stock'          => $item->stock,
            'weight'         => $item->weight,
            'color'          => $item->color,
            'size'           => $item->size,
            'image'          => $item->image,
            'is_in_stock'     => $item['is_in_stock'] ?? true,
            'current_stock'   => $item['current_stock'] ?? $item['stock'],
        ]]);
    }

    /**
     * همگام‌سازی قیمت/موجودی ردیف‌های سبد دیتابیس با قیمت روز محصول/واریانت.
     * قیمت تا قبل از نهایی‌شدن سفارش باید با سایت یکی باشد (قفل قیمت فقط بعد از ثبت سفارش).
     */
    private function refreshDbPrices(Collection $items): void
    {
        $productIds = $items->pluck('product_id')->unique()->filter()->all();
        if (empty($productIds)) {
            return;
        }

        $products = Product::with('variants')->findMany($productIds);

        foreach ($items as $item) {
            $product = $products->get($item->product_id);
            if (! $product) {
                continue;
            }

            // ترمیم ردیف‌های قدیمیِ بدون واریانت (محصول واریانت‌دار که قبل از این فیکس اضافه شده)
            if (empty($item->variant_id) && $product->has_variants) {
                $default = $product->variants->sortByDesc('stock')->firstWhere(fn ($v) => $v->stock > 0)
                    ?? $product->variants->first();
                if ($default) {
                    $item->variant_id = $default->id;
                    $item->variant_label = $default->label ?: $item->variant_label;
                }
            }

            $variant = ! empty($item->variant_id)
                ? $product->variants->firstWhere('id', $item->variant_id)
                : null;

            $freshPrice = $variant ? $variant->final_price : $product->final_price;
            $freshOriginal = $variant ? $variant->price : $product->price;
            $freshStock = $variant ? (int) $variant->stock : (int) $product->stock;

            if ((int) $item->price !== (int) $freshPrice
                || (int) $item->original_price !== (int) $freshOriginal
                || (int) $item->stock !== $freshStock
                || $item->isDirty('variant_id')) {
                $item->price = $freshPrice;
                $item->original_price = $freshOriginal;
                $item->stock = $freshStock;
                $item->save();
            }
        }
    }

    private function dbAdd(Product $product, int $qty, ?int $variantId, ?string $color, ?string $size): string
    {
        $variant = $variantId ? $product->variants()->find($variantId) : null;
        $price = $variant ? $variant->final_price : $product->final_price;
        $originalPrice = $variant ? $variant->price : $product->price;
        $stock = $variant ? $variant->stock : $product->stock;
        $weight = $variant ? ($variant->weight ?? 0) : ($product->weight ?? 0);
        $label = $variant && $variant->specifications->isNotEmpty()
            ? $variant->specifications
                ->sortBy(fn ($s) => $s->parent_id ?? 0)
                ->map(fn ($s) => $s->trans('title'))
                ->implode(' / ')
            : null;

        $rowId = md5($product->id . ($variantId ?? '') . ($color ?? '') . ($size ?? ''));

        $existing = Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->where('variant_id', $variantId)
            ->where('color', $color)
            ->where('size', $size)
            ->first();

        if ($existing) {
            $existing->update(['qty' => $existing->qty + $qty]);
            return (string) $existing->id;
        }

        $cart = Cart::create([
            'user_id'        => auth()->id(),
            'product_id'     => $product->id,
            'variant_id'     => $variantId,
            'variant_label'  => $label,
            'name'           => $product->trans('name') ?? $product->name,
            'slug'           => $product->slug,
            'price'          => $price,
            'original_price' => $originalPrice,
            'qty'            => $qty,
            'stock'          => $stock,
            'weight'         => $weight,
            'color'          => $color,
            'size'           => $size,
            'image'          => $product->images->first()->url ?? null,
        ]);

        return (string) $cart->id;
    }

    private function dbUpdate(string $rowId, int $qty): void
    {
        Cart::where('user_id', auth()->id())->where('id', $rowId)->update(['qty' => max(1, $qty)]);
    }

    private function dbRemove(string $rowId): void
    {
        Cart::where('user_id', auth()->id())->where('id', $rowId)->delete();
    }

    private function dbClear(): void
    {
        Cart::where('user_id', auth()->id())->delete();
    }

    // ─── سشن (مهمان) ─────────────────────────────────────────────────

    private function sessionContent(): Collection
    {
        $cart = collect(session($this->sessionKey, []));

        $changed = false;
        $productIds = $cart->pluck('product_id')->unique()->filter()->all();
        $products = empty($productIds) ? collect() : Product::with('variants')->findMany($productIds);

        $cart = $cart->map(function ($item) use ($products, &$changed) {
            $product = $products->get($item['product_id'] ?? null);
            if (! $product) {
                return $item;
            }

            // ترمیم ردیف‌های قدیمیِ بدون واریانت + همگام‌سازی قیمت/موجودی با قیمت روز
            if (empty($item['variant_id']) && $product->has_variants) {
                $default = $product->variants->sortByDesc('stock')->firstWhere(fn ($v) => $v->stock > 0)
                    ?? $product->variants->first();
                if ($default) {
                    $item['variant_id'] = $default->id;
                    $item['variant_label'] = $default->label ?: ($item['variant_label'] ?? null);
                    $changed = true;
                }
            }

            $variant = ! empty($item['variant_id'])
                ? $product->variants->firstWhere('id', $item['variant_id'])
                : null;

            $freshPrice = $variant ? $variant->final_price : $product->final_price;
            $freshOriginal = $variant ? $variant->price : $product->price;
            $freshStock = $variant ? (int) $variant->stock : (int) $product->stock;

            if ((int) ($item['price'] ?? 0) !== (int) $freshPrice
                || (int) ($item['original_price'] ?? 0) !== (int) $freshOriginal
                || (int) ($item['stock'] ?? 0) !== $freshStock) {
                $item['price'] = $freshPrice;
                $item['original_price'] = $freshOriginal;
                $item['stock'] = $freshStock;
                $changed = true;
            }

            return $item;
        });

        if ($changed) {
            session([$this->sessionKey => $cart->toArray()]);
        }

        return $cart;
    }

    private function sessionAdd(Product $product, int $qty, ?int $variantId, ?string $color, ?string $size): string
    {
        $cart = $this->sessionContent();
        $rowId = md5($product->id . ($variantId ?? '') . ($color ?? '') . ($size ?? ''));

        $variant = $variantId ? $product->variants()->find($variantId) : null;
        $price = $variant ? $variant->final_price : $product->final_price;
        $originalPrice = $variant ? $variant->price : $product->price;
        $stock = $variant ? $variant->stock : $product->stock;
        $weight = $variant ? ($variant->weight ?? 0) : ($product->weight ?? 0);
        $label = $variant && $variant->specifications->isNotEmpty()
            ? $variant->specifications
                ->sortBy(fn ($s) => $s->parent_id ?? 0)
                ->map(fn ($s) => $s->trans('title'))
                ->implode(' / ')
            : null;

        if ($cart->has($rowId)) {
            $item = $cart[$rowId];
            $item['qty'] += $qty;
            $cart[$rowId] = $item;
        } else {
            $cart[$rowId] = [
                'rowId'          => $rowId,
                'product_id'     => $product->id,
                'variant_id'     => $variantId,
                'variant_label'  => $label,
                'name'           => $product->trans('name') ?? $product->name,
                'slug'           => $product->slug,
                'price'          => $price,
                'original_price' => $originalPrice,
                'qty'            => $qty,
                'stock'          => $stock,
                'weight'         => $weight,
                'color'          => $color,
                'size'           => $size,
                'image'          => $product->images->first()->url ?? null,
            ];
        }

        session([$this->sessionKey => $cart->toArray()]);

        return $rowId;
    }

    private function sessionUpdate(string $rowId, int $qty): void
    {
        $cart = $this->sessionContent();

        if ($cart->has($rowId)) {
            $item = $cart[$rowId];
            $item['qty'] = max(1, $qty);
            $cart->put($rowId, $item);
            session([$this->sessionKey => $cart->toArray()]);
        }
    }

    private function sessionRemove(string $rowId): void
    {
        $cart = $this->sessionContent();
        $cart->forget($rowId);
        session([$this->sessionKey => $cart->toArray()]);
    }

    private function sessionClear(): void
    {
        session()->forget($this->sessionKey);
    }

    // ─── متدهای عمومی ───────────────────────────────────────────────

    public function add(Product $product, int $qty = 1, ?int $variantId = null, ?string $color = null, ?string $size = null): string
    {
        $qty = max(1, $qty);

        if ($this->isAuth()) {
            return $this->dbAdd($product, $qty, $variantId, $color, $size);
        }

        return $this->sessionAdd($product, $qty, $variantId, $color, $size);
    }

    public function update(string $rowId, int $qty): void
    {
        if ($this->isAuth()) {
            $this->dbUpdate($rowId, $qty);
            return;
        }

        $this->sessionUpdate($rowId, $qty);
    }

    public function remove(string $rowId): void
    {
        if ($this->isAuth()) {
            $this->dbRemove($rowId);
            return;
        }

        $this->sessionRemove($rowId);
    }

    public function clear(): void
    {
        if ($this->isAuth()) {
            $this->dbClear();
            return;
        }

        $this->sessionClear();
    }

    public function mergeGuestCart(array $guestItems): void
    {
        if (! $this->isAuth() || empty($guestItems)) {
            return;
        }

        foreach ($guestItems as $item) {
            $product = Product::find($item['product_id'] ?? null);
            if (! $product) {
                continue;
            }

            $qty = max(1, (int) ($item['qty'] ?? 1));
            $variantId = $item['variant_id'] ?? null;
            $color = $item['color'] ?? null;
            $size = $item['size'] ?? null;

            $variant = $variantId ? $product->variants()->find($variantId) : null;
            $stock = $variant ? $variant->stock : $product->stock;

            $existing = Cart::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->where('variant_id', $variantId)
                ->where('color', $color)
                ->where('size', $size)
                ->first();

            if ($existing) {
                $newQty = min($existing->qty + $qty, $stock);
                $existing->update(['qty' => $newQty]);
            } else {
                $this->dbAdd($product, $qty, $variantId, $color, $size);
            }
        }

        $this->sessionClear();
    }

    public function getSessionItems(): array
    {
        return session($this->sessionKey, []);
    }

    public function subtotal(): float
    {
        return (float) $this->content()->sum(fn ($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));
    }

    public function discount(): float
    {
        $coupon = session('coupon');

        if (! $coupon) {
            return 0;
        }

        $couponService = app(CouponService::class);

        return $couponService->calculateForCart($this);
    }

    public function total(): float
    {
        return max(0, $this->subtotal() - $this->discount());
    }

    public function count(): int
    {
        return (int) $this->content()->sum('qty');
    }

    public function totalWeight(): float
    {
        return $this->content()->sum(fn ($item) => ($item['weight'] ?? 0) * $item['qty']);
    }

    public function roundedWeight(): float
    {
        $weight = $this->totalWeight();
        return ceil($weight / 100) * 100;
    }

    public function applyCoupon(string $code): bool
    {
        $couponService = app(\App\Services\CouponService::class);
        $result = $couponService->apply($code, $this->subtotal());

        return $result['success'];
    }

    public function toArray(): array
    {
        return $this->content()->values()->toArray();
    }
}