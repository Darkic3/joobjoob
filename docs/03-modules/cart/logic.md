# Cart — Logic (Enriched Phase 2.1)

> **Source of Truth:** `app/Services/CartService.php:1-560`, `app/Http/Controllers/Site/CartController.php:1-160`, `app/Models/Cart.php:1-35`, `app/Services/CouponService.php:calculateForCart`, `app/Services/StockService.php:enrichCartItemsWithStockStatus`

## 1. Purpose & Responsibilities
Cart yek service-e do-gane hast: **Guest = session** `cart` (array keyed by md5) + **Auth = DB** `carts` table. Hameye mohasebat (subtotal, discount, total, count, weight) dar CartService jam shode va Checkout ham session keys `checkout_*` + `coupon` ra estefade mikonad.

## 2. Controllers (Actual)
**Site/CartController (`app/Http/Controllers/Site/CartController.php`)**
- `__construct(CartService $cart)` DI.
- `index(Request)` — evidence line 14-45: agar `cart->count()==0` view ba `addresses=collect(), states=[], banks=collect(), isGuest` baz migarde (khali). Agar `isGuest` ( `!auth()->check()` ) faghat `items = cart->content()` + `isGuest=true` (step 1 faghat). Agar auth: `AddressService::find()['addresses']`, `StateService::findActive()`, `Bank::active()->orderBy(id DESC)` + `session checkout_address_id/shipping_method_id` load baraye selectedAddress/Shipping.
- `add(Request, product)` — evidence ~46-110: `Product::where(slug/id)->firstOrFail()`, validate qty, `cart->add(product, qty, variantId, color, size)` -> rowId md5, `stockWarning` agar qty > stock, response JSON `added_to_cart/stock_limit_reached` + `cart_count` ya redirect back with flash.
- `update(Request, rowId)` — evidence ~112-145: `validate qty integer min:1`, `content()->get(rowId)`, `$maxQty = item['stock']`, `$qty=min(requested, maxQty)`, `cart->update(rowId,qty)`, JSON `subtotal/discount/total/cart_count/stock_warning` ba `wantsJson`.
- `remove(Request, rowId)` — evidence ~147-165: `cart->remove(rowId)`, JSON `subtotal/discount/total/cart_count/is_empty`.
- `applyCoupon(Request)` — evidence ~167-173: `validate code required|string`, `$ok=cart->applyCoupon(code)`, back with success/error `coupon_applied/coupon_invalid`.
- `OrderService` va `BankPaymentService` dar checkout `create` seda zade mishavand, na inja.

**Site/CheckoutController (`site.php` checkout group)**
- `shipments(Request)` — `AddressService::findOne(address_id)` + `session[checkout_address_id]=id` + `ShippingCalculator::getAvailableMethods(address, cart->roundedWeight(), cart->total())`
- `setShipment(Request)` — `session[checkout_shipping_method_id, checkout_shipping_price]`
- `addressPrice()` / `orderPrice()` — `cart->subtotal()/discount()/total() + intval(shippingPrice)`

## 3. Service — CartService (`app/Services/CartService.php`)
### 3.1 sessionKey & isAuth
- `protected string $sessionKey='cart'` (line 12). `private isAuth():bool => auth()->check()` — evidence 16-19.

### 3.2 content():Collection branches
- `content()` — if isAuth `dbContent()` else `sessionContent()`.

### 3.3 dbContent() (Auth DB)
- `Cart::where(user_id, auth()->id())->get()` — line ~26.
- **Backfill original_price**: agar `empty(original_price)` → `Product::find(product_id)` -> `variant = product->variants()->find(variantId)` -> `original_price = variant?price:product->price`, `needsUpdate` save if `getOriginal` null.
- **Enrich stock**: `$stockService->enrichCartItemsWithStockStatus(items)` — har item `getAvailableStock` ba `Product::withTrashed()->find` (N+1) -> `is_in_stock/current_stock`.
- **Map**: `mapWithKeys id => [rowId=>id, product_id, variant_id, variant_label, name, slug, price, original_price, qty, stock, weight, color, size, image, is_in_stock, current_stock]`.

### 3.4 dbAdd(Product,qty,variantId,color,size):string
- `variant = product->variants()->find(variantId)` (nullable).
- `price = variant?final_price:product->final_price`, `originalPrice = variant?price:product->price`, `stock = variant?stock:product->stock`, `weight = variant?weight:product->weight`.
- `label = variant spec trans title join ' / ' sorted by parent_id`.
- `rowId = md5(product.id . variantId . color . size)` — string.
- `existing = Cart::where(user_id, product_id, variant_id, color, size)->first()` -> if exists `update qty+qty`, else `Cart::create([... user_id, product_id, variant_id, variant_label, name=trans(name), slug, price, original_price, qty, stock, weight, color,size,image=first url])` -> return `string id`.

### 3.5 dbUpdate / dbRemove / dbClear
- `dbUpdate(rowId,qty): void` — `Cart::where(user_id, id=rowId)->update(['qty'=>max(1,qty)])`.
- `dbRemove` — delete where id, `dbClear` — delete where user_id.

### 3.6 sessionContent() / sessionAdd / sessionUpdate / sessionRemove / sessionClear
- `session($sessionKey,[])` collect. Same backfill + no DB save (session array).
- `sessionAdd` — same price/stock/label logic, `rowId=md5(...)`, if `has(rowId)` increment qty else put, `session([cart=>toArray])`.
- `sessionUpdate` / `sessionRemove` / `sessionClear` — array manipulation + `session([cart=>...])`.

### 3.7 Aggregates
- `subtotal():int` — sum `price*qty` over `content()`.
- `discount():float` — `app(CouponService)->calculateForCart($this)` (tavasote pay_type/with_discount filter — see discounts workflow).
- `total():float` — `subtotal - discount`.
- `count():int` — sum qty, `weight():int` — sum weight*qty, `roundedWeight():float` — ceil(weight/100)*100? Actually in ShippingCalculator `ceil(weightSum/100)*100` for with_weight.
- `toArray():array` — content values array for OrderService create.
- `clear():void` — if isAuth dbClear else sessionClear.
- `mergeGuestCart(array guestItems)` — mojood vali **hichi seda nemizane** — UNKNOWN (grep -rn mergeGuestCart faghate tarif). Guest cart bad az login momkene gom she.
- `applyCoupon(code):bool` — deleg be CouponService apply (upper trim, checks), `removeCoupon`.

### 3.8 Stock interaction
- `enrichCartItemsWithStockStatus` per item `getAvailableStock(productId, variantId)` -> `ProductVariant::withTrashed()->find` or `Product::withTrashed()->find` -> `max(0, stock)` cast int.

## 4. Model — carts (`app/Models/Cart.php`)
- `fillable: user_id, product_id, variant_id, variant_label, name, slug, price, original_price, qty, stock, weight, color, size, image`.
- `relations: user belongsTo(User), product belongsTo(Product)`.
- DB: `carts: id, user_id FK nullOnDelete index, product_id FK cascade, variant_id nullable, variant_label, name, slug, price unsignedInt, original_price nullable, qty default1, stock default0, weight default0, color, size, image, timestamps` — no softDeletes. Guest cart even via session.

## 5. Validation & Auth
- `CartController::add` — no FormRequest, inline `Product slug exists`, qty integer min1. Guest mojaze add (session-based).
- `update` — `qty required|integer|min:1`, plus custom maxQty clamp.
- `applyCoupon` — `code required|string`, middleware `auth` for `POST /cart/coupon` (site.php line `middleware auth post /cart/coupon`).
- Guest can view/update/remove cart bedoone auth; checkout steps require auth (group `auth` prefix checkout).

## 6. Business Rules
- Guest vs Auth dual — hich sync automatic nist (UNKNOWN merge).
- `rowId` md5 deterministic — debug sade vali collision risk nist (4 field).
- Price snapshot dar cart (price/original_price) az product/variant dar zaman add — agar product price taghir kone cart price dige update nemishe (snapshot).
- `is_in_stock/current_stock` enrich faghat baraye namayesh — reserve dar `OrderService::create -> StockService::reserveStock` ba `lockForUpdate` anjam mishe, na dar cart.

## 7. Transactions / Cache / Session
- CartService khod transaction nadarad (read/write simple). Reserve dar OrderService ba `DB::transaction`.
- Session keys: `cart`, `coupon` (array code/discount_id/type/value/discount_amount), `checkout_address_id`, `checkout_shipping_method_id`, `checkout_shipping_price`.
- Cache: nadarad.

## 8. Failure Paths
- Product not found `firstOrFail 404`.
- Qty > stock -> clamp + warning `stock_limit_reached`, not error.
- Guest cart lost after login if merge not called — UNKNOWN.

## 9. Known Bugs / Legacy
- **N+1 Product::find per item** in dbContent/sessionContent + enrich — `withTrashed()->find` per item. Mitune `whereIn`.
- **mergeGuestCart no caller** — evidence `grep -rn mergeGuestCart app --include=*.php` faghate method tarif.
- **Snapshot price** — stale if catalog price changes.
- **SoftDeletes not used** for carts — guest session even has no id.

## 10. Evidence
- `app/Services/CartService.php:12 sessionKey, 16 isAuth, 26 dbContent, 80 dbAdd, 140 dbUpdate, 180 sessionContent, 200 sessionAdd, 260 subtotal/discount, 340 mergeGuestCart`
- `app/Http/Controllers/Site/CartController.php:14 index, 46 add, 112 update, 147 remove, 167 applyCoupon`
- `app/Models/Cart.php:8 fillable`
- `routes/site.php: POST /cart/add/{product} name cart.add (guest allowed), prefix cart GET / index PATCH update DELETE remove, POST /cart/coupon auth`
