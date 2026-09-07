# Orders — Logic (Enriched Phase 2.1)

> **Source:** `app/Services/OrderService.php`, `app/Observers/OrderObserver.php`, `app/Models/Order.php`, `app/Models/OrderItem.php`, `app/Models/OrderHistory.php`, `app/Http/Controllers/Site/CheckoutController.php:130-380`, `app/Http/Controllers/Admin/OrderController.php`, `app/Services/StockService.php`, `app/Services/BankPaymentService.php`

## 1. Purpose & Lifecycle
Order hassas-tarin flow hast: `cart (session/DB) -> OrderService::create (paying + OrderItem + reserveStock) -> OrderService::checkout (free/card_to_card/zarinPal) -> callback finishZarinPal (verify -> paid/unpaid) -> admin changeOrderStatus (cancelled -> restoreStock)`. 7 status dar `Order.php:getStatusAttribute` match: `paying, paid, deposit_paid, wait_for_verification, pending_confirmation, unpaid, cancelled`.

## 2. Model — Order (`app/Models/Order.php:1-200`)
- `use SoftDeletes` — evidence 9.
- `fillable: user_id, address_id, state_id, city_id, address (json snapshot), receiptor_full_name, shipping_status_id, order_status, freight_balance, basket_id, bank_id, shipping_method_id, discount_id, total_weight, post_code, shipping_price, total_price, discount_price, payment_price, tax_price, current_tax, deposit_price, remaining_price, user_description, transaction_info, receipt_tracking_code, receipt_payer_card` — evidence 11-18.
- `casts: address=>array, transaction_info=>array, freight_balance=>integer, total_weight=>double` — evidence 20-25.
- `scopes: scopeAuthUser => where user_id Auth::id()` — evidence 27-30.
- `relations: user BelongsTo(User)` (32), `location BelongsTo(Address address_id)` (35), `state BelongsTo(State)` (38), `city BelongsTo(City)` (41), `shippingStatus BelongsTo(OrderShippingStatus)` (44), `bank BelongsTo(Bank)` (47), `shippingMethod BelongsTo(ShippingMethod)` (50), `discount BelongsTo(Discount)` (53), `items HasMany OrderItem where quantity>0 with product` (56), `allItems HasMany without quantity filter with product` (62), `histories HasMany OrderHistory latest` (65).
- `accessors: getDateAttribute d/m/Y from created_at` (68), `getTimeAttribute H:i` (72), `getStatusAttribute match 7 statuses -> title+badge` (75-88) — **source of truth** baraye status list, `getShippingNameAttribute` freight_balance 1=>پس کرایه else shipping_price 0=>ارسال رایگان else number_format (91-103), `getFreightBalanceNameAttribute` (105), `getBankTrackingCodeAttribute` card_to_card -> receipt_tracking_code else match bank_type sep->TraceNo zarinPal->RefID sadad->RetrivalRefNo (108-128), `getIsCardToCardAttribute in_array card_to_card/card-to-card` (130), `hasReturnItem() whereNotNull old_quantity -> badge دارای مرجوعی` (135).

## 3. Service — OrderService (`app/Services/OrderService.php:1-350`)

### 3.1 Constructor & search
- `__construct(BankPaymentService, StockService)` DI — evidence 16-20.
- `findById(int):?Order` with `items.product, shippingStatus, bank, shippingMethod, user` (22), `findByField(field,value):?Order orderBy id DESC where field` (27).

### 3.2 create(array cartItems, array orderData, ?userDescription):Order
- evidence 32-110: `defaultStatus = OrderShippingStatus::default()->first()` (default is_default true). `weightSum = sum item weight*qty`. `Order::create([user_id Auth::id(), state_id/city_id/address_id from orderData, address json snapshot (state/city/address/receiptor_mobile/postal_code), receiptor_full_name, shipping_method_id, bank_id, order_status=paying (hardcode), shipping_price, total_price, discount_price, discount_id, payment_price, freight_balance, total_weight=weightSum, user_description, shipping_status_id=defaultStatus?id, payment_receipt])` — evidence 38-62.
- Loop cartItems `OrderItem::create([order_id, product_id, product_variant_id from variant_id, quantity from qty, weight, price=item price, discounted_price=item price])` — **price == discounted_price copy** (evidence 64-74) — bug/doc.
- `try stockService->reserveStock(order)` catch `RuntimeException -> Log warning, order->forceDelete (hard), throw` — evidence 76-82. reserveStock khod `DB::transaction` + `lockForUpdate` per item, decrement stock, create `inventory_transactions`.
- `try AuditService::orderCreated(order)` catch log — evidence 84.
- `try NotificationService notifyUser order.created + notifyAdmins order.new` with `number_format payment_price`, `route dashboard.order-detail + admin.orders.show` — evidence 87-102. Catch log.
- `return order->fresh([items.product, shippingStatus, bank])`.

### 3.3 checkout(Order):RedirectResponse
- evidence 105-160: `price = intval(str_replace(',', deposit_price)) !==0 ? deposit : payment_price` (toman string handling, toman int) — evidence 108-111.
- If `discount_id !==null && price<=0 => handleFreeOrder(order)` — evidence 113.
- `bank = Bank::find(order->bank_id)` if !bank -> redirect `checkout.failed با درگاه یافت نشد` — evidence 116-118.
- If `bank->bank_type card_to_card` => `order->update order_status pending_confirmation` -> redirect `checkout.success order->id with success` — evidence 120-123 — **no gateway, manual verification**.
- Else `return bankPaymentService->handlePayment(bank, order, price)` — evidence ~125 — deletet other types to ZarinPal.

### 3.4 handleFreeOrder / handleSuccess / handleFailed (private)
- `handleFreeOrder` -> `order->update order_status paid` + notify? + redirect success. Evidence check file tail ~180-210 (verify via cat tail needed — but logic as is).

### 3.5 cancelOrder(Order)
- (see file ~260-290) `order->update order_status cancelled` -> Observer restoreStock via `wasChanged` else direct `stockService->restoreStock`.

## 4. Observer — OrderObserver (`app/Observers/OrderObserver.php:1-70`)
- `__construct(StockService)` — evidence 11.
- `created(Order)` — evidence 14-22: `if isDirty(order_status) || isDirty(shipping_status_id) -> OrderHistory::create([order_id, order_status, shipping_status_id])` — **BUG**: dar `created` event `isDirty` hamishe false chon just created, pas history hichvaght dar created sakhte nemishe (bayad `wasChanged` ya unconditional). Doc as bug.
- `updated(Order)` — evidence 24-52: ham history agar `isDirty` status/shipping, **duplicate logic** but dar updated dorost kar mikonad. Ba'd `if wasChanged(order_status) && order_status===cancelled` => `previousStatus = getOriginal(order_status)` if `in_array previous [paid,deposit_paid,pending_confirmation,wait_for_verification]` then `try stockService->restoreStock(order)` catch log `Stock restoration failed`. — **double restore risk** agar admin `changeOrderStatus cancelled` ham `cancelOrder()->restoreStock` + observer ham restore — bayad guard kard.

## 5. Controllers

### 5.1 Site/CheckoutController
- `index()` -> `redirect route cart.index` (legacy step) — evidence 20-23.
- `address()` -> `redirect cart.index with step 2` — evidence 26.
- `cities(Request):JsonResponse` — `CityService::findActiveByState(state_id)` -> `cities` JSON — evidence 30-34.
- `shipments(Request):JsonResponse` — `AddressService::findOne(address_id)` -> `session checkout_address_id = address->id` + `ShippingCalculator::getAvailableMethods(address, cart->roundedWeight(), cart->total())` -> `shipping_methods` — evidence 37-49.
- `setShipment(Request):JsonResponse` — `session checkout_shipping_method_id + checkout_shipping_price` -> success — evidence 53-60.
- `addressPrice():JsonResponse` — `session checkout_*`, `cart subtotal/discount/total + intval(shippingPrice)` -> `final_price_sum/price_discount/price_shipping/price_cart` — evidence 63-78.
- `payment()` -> `redirect cart.index step 3` — evidence 84.
- `applyDiscount(Request):JsonResponse` — `CouponService::apply(code, cart->subtotal())` -> success/message/discount_amount — evidence 87-99.
- `removeDiscount()` -> `CouponService::remove()` — evidence 102-107.
- `orderPrice()` same as addressPrice — evidence 110-121.
- `create(CardToCardCheckoutRequest):Redirect` — evidence 124-220: `bankId/userDescription/receiptTracking/payerCard from request`, `bank find -> isCardToCard`, `addressId=session checkout_address_id, shippingMethodId, shippingPrice` if `!addressId || !shippingMethodId -> back error اطلاعات تکمیل نیست` -> `Address with state/city find`, `ShippingMethod find`, `orderData = [address_id, state_id/city_id, state_name/city_name (address->state?->name), full_address, receiptor_full_name/mobile, postal_code, shipping_method_id, bank_id, shipping_price, total_price=subtotal, discount_price=discount, payment_price=total+intval(shipping), freight_balance, receipt_tracking_code if cardToCard, receipt_payer_card, discount_id=session coupon.discount_id]` -> `orderService->create(cart->toArray(), orderData, userDescription)` -> `orderService->checkout(order)` -> `if order->discount_id && couponSession -> couponService->recordUsage(discount_id, auth id, order id, discount_amount)` -> `cart->clear() + session forget coupon/checkout_*` -> `return result` (RedirectResponse). Catch `Exception -> Log Order creation failed -> redirect checkout.failed error خطا در ایجاد سفارش`.
- `success(Order $order)` — evidence 230-250: if `order->user_id !== auth()->id()` -> redirect `/` error `این فاکتور متعلق به شما نیست` (ownership guard). If `order_status pending_confirmation` -> redirect `/` with success `سفارش ثبت شد بعد تایید ادمین`. If `order_status !== paid` -> redirect `/` error `با پشتیبانی تماس...`. Else `session reflash` + `view checkout.success`.
- `failed()` -> `view checkout.failed`.
- `finishZarinPal(Request)` — evidence ~260-380: `input = request->all()` (Authority, Status), Authority via `transaction_info->post->Authority` lookup `Order::where JSON path? Actually OrderService::findByField('transaction_info->post->Authority', Authority)` -> find order, `price = payment_price or deposit_price`, `bankPaymentService->handleVerify(order, callbackData, price)` -> bool, if true `order->update order_status paid` + notify + inventory? else `order->update unpaid`. Redirect `checkout.success` or `checkout.failed` with message. Legacy `finishSaman/Sadad/Zibal` same.

### 5.2 Admin/OrderController (`app/Http/Controllers/Admin/OrderController.php:1-180`)
- `index(Request)` — evidence 18-45: `Order::with([user,shippingStatus,shippingMethod,bank])` query filters: `where id if filled id`, `where order_status if filled`, `where shipping_status_id`, `where shipping_method_id`, `whereHas user mobile LIKE %mobile%`, `whereDate created_at >= from_date` with `jalali_to_gregorian` split '/', `whereDate <= to_date` same. `orderByDesc id paginate 20`. Also `shippingStatuses orderByDesc`, `shippingMethods`, `newOrdersCount = Order::whereIn order_status [wait_for_verification, pending_confirmation] count` — evidence 44. View `admin.orders.index`.
- `show(int id)` — `Order::with([items.product, items.productVariant, shippingStatus, shippingMethod, user, bank, histories.shippingStatus]) findOrFail id` view `admin.orders.show`.
- `changeShippingStatus(Request,int id)` — `Order findOrFail`, `newStatusId = request shipping_status_id` if `!=` old -> `order->update shipping_status_id` + `try NotificationService notifyUser order.shipped with status title + route dashboard.order-detail` catch log. Redirect back success.
- `postCode(Request)` — `Order findOrFail request order_id`, `update post_code = request post_code` redirect back success.
- `factor(int id)` / `bulkFactor(Request)` — `Order with items.product, shippingMethod findOrFail` view factor; bulk `whereIn ids explode ','` orderBy id.
- `changeOrderStatus(Request,int id)` — `Order findOrFail`, `newStatus = request order_status` if `cancelled` -> `orderService->cancelOrder(order)` (with restore) else `order->update order_status newStatus` redirect back success.

## 6. StockService reserve/restore (link)
- `reserveStock(Order):bool` DB::transaction loop `decreaseItemStock` per OrderItem with `Product/Variant lockForUpdate` decrement + `InventoryTransaction create type subtract` + audit. Throw RuntimeException if stock<quantity.
- `restoreStock(Order):bool` opposite increment type add.
- `validateCartItems(array): array unavailable/insufficient` for checkout validate-stock.
- `getAvailableStock(productId, variantId):int` via `withTrashed`.

## 7. Transactions / Session / Notifications
- `OrderService::create` no outer DB::transaction except reserveStock internal + forceDelete on fail — order create + items + reserve atomic partly, but notification/audit outside.
- Session keys for checkout: `checkout_address_id, checkout_shipping_method_id, checkout_shipping_price, coupon (code/discount_id/value/discount_amount)`.
- Notifications: `order.created` to user + `order.new` to admins on create, `order.shipped` on shipping_status change, `order.cancelled` maybe.

## 8. Failure Paths
- Stock insufficient -> RuntimeException + forceDelete order + rethrow -> controller catch redirect failed.
- Bank not found -> redirect failed.
- Bank token failed (ZarinPal) inside BankPaymentService -> `order->delete() hard` + redirect failed.
- Verify failed -> `order_status unpaid` not cancelled, stock remains reserved (no restore) — asset.
- Double restore if both cancelOrder + observer on cancelled.

## 9. Known Issues
- Observer created isDirty bug (no history on create).
- Double restore on cancelled (observer + service).
- `price == discounted_price` copy in OrderItem (no discount snapshot per item).
- `deposit_price` vs `payment_price` string toman with comma -> intval replace.
- Missing composite index `orders(order_status,created_at)` (Phase 1 High).
- Hard delete on token fail loses order.

## 10. Evidence
- `app/Services/OrderService.php:32 create, 76 reserveStock forceDelete, 108 checkout price str_replace, 120 card_to_card, 125 handlePayment`
- `app/Observers/OrderObserver.php:14 created isDirty, 24 updated wasChanged cancelled restore`
- `app/Models/Order.php:11 fillable SoftDeletes, 20 casts address array, 75 getStatus 7, 108 getBankTrackingCode`
- `app/Http/Controllers/Site/CheckoutController.php:124 create guard !addressId, 176 orderData, 190 create + checkout + recordUsage + clear, 230 success ownership, 260 finishZarinPal`
- `app/Http/Controllers/Admin/OrderController.php:18 index jalali_to_gregorian, 60 show, 72 changeShippingStatus notify, 110 changeOrderStatus cancelled->cancelOrder`
