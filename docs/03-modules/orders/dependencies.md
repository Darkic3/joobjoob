# Orders — Dependencies

> **Source:** `app/Services/OrderService.php`, `app/Services/StockService.php`, `app/Services/BankPaymentService.php`, `app/Observers/OrderObserver.php`, `app/Http/Controllers/Site/CheckoutController.php`, `app/Http/Controllers/Admin/OrderController.php`, `app/Services/CartService.php`, `app/Services/CouponService.php`, `app/Payments/*`, `app/Models/*`, `routes/*`
> **Desc:** OrderService -> Bank/Stock/OrderShippingStatus/Notification/Audit؛ Observer -> Stock/History؛ Controllers -> Services؛ Cart/Address/Shipping/Coupon upstream است.

## Dependency Graph

```
Site: CheckoutController --depends--> OrderService --depends--> BankPaymentService --depends--> PaymentGatewayFactory --creates--> ZarinPal
                                    \-> StockService --depends--> Product/ProductVariant (lockForUpdate) + InventoryTransaction + AuditService
                                    \-> NotificationService (notifyUser/notifyAdmins)
                                    \-> AuditService (orderCreated/orderStatusChanged)
                                    \-> Order / OrderItem / OrderShippingStatus / Bank
                     \-> CartService (subtotal/discount/total/toArray/roundedWeight/clear)
                     \-> AddressService / CityService / ShippingCalculator / CouponService
Admin: OrderController --depends--> OrderService (cancelOrder)
                    \-> Order / OrderShippingStatus / ShippingMethod
                    \-> NotificationService (changeShippingStatus)
Observer: OrderObserver --depends--> StockService (restoreStock) + OrderHistory
Cron: CancelExpiredOrders --depends--> Order + StockService
Payments: ZarinPal --depends--> Http + Log + PaymentPostDTO/PaymentVerifyDTO + Bank.config(MerchantId)
```

## Direct Dependencies

| Module/Service | Used by | How | Evidence |
|----------------|---------|-----|----------|
| **OrderService** | CheckoutController, Admin OrderController, Callback | `app(OrderService::class)->create/checkout/callback/cancelOrder/handleSuccess` | `CheckoutController:200,220`, `Admin OrderController:85`, `OrderService:168-250` |
| **StockService** | OrderService, OrderObserver, CheckoutController.validateStock, CancelExpiredOrders, CartService.enrich | `reserveStock, restoreStock, validateCartItems, getAvailableStock` + `lockForUpdate` + `InventoryTransaction` | `StockService:24-200`, `OrderService:79`, `Observer:28`, `CheckoutController:260` |
| **BankPaymentService** | OrderService | `handlePayment(bank,order,price)->getBankToken+redirect`, `handleVerify(order,data,price)->verifyTransaction` | `BankPaymentService:20-60,64-95` |
| **PaymentGatewayFactory** | BankPaymentService | `create(bank, siteName)` match `zarinPal/card_to_card=>ZarinPal` | `PaymentGatewayFactory:12-18` |
| **ZarinPal (PaymentGateway)** | Factory | `getBankToken, verifyTransaction, redirect` + `Http post` + `code 100 / 100||101` + `amount rial x10` | `Payments/ZarinPal.php:20-90` |
| **OrderObserver** | Eloquent events | `created(isDirty bug)`, `updated(history + wasChanged cancelled restoreStock)` | `Observers/OrderObserver.php:14-50` |
| **NotificationService** | OrderService, Admin OrderController | `notifyUser(type,title,body,url,meta)`, `notifyAdmins(...)` types `order.created/new/paid/paid_admin/shipped/cancelled` | `OrderService:88-105,150-170,220`, `Admin OrderController:58-75` |
| **AuditService** | OrderService, StockService | `orderCreated, orderStatusChanged, productStockChanged` (swallow) | `AuditService:33,70,119`, `OrderService:86,145,195` |
| **CartService** | CheckoutController | `subtotal(), discount(), total(), totalWeight(), roundedWeight(), toArray(), clear(), content(), mergeGuestCart` — session+DB dual | `CartService:290 mergeGuestCart`, `CheckoutController:12 constructor` |
| **AddressService / CityService** | CheckoutController | `AddressService::findOne`, `CityService::findActiveByState(state_id)` | `CheckoutController:28,42` |
| **ShippingCalculator** | CheckoutController | `getAvailableMethods(address, roundedWeight, total)` | `CheckoutController:46` |
| **CouponService** | CheckoutController | `apply(code,subtotal), remove(), calculateForCart, recordUsage(discount_id,user_id,order_id,amount)` | `CheckoutController:90,120,210` |

## Models & Cross-module Coupling

| Model | Depends on | Via |
|-------|------------|-----|
| Order | User, Address, State, City, OrderShippingStatus, Bank, ShippingMethod, Discount, Product(via items) | `belongsTo` + `hasMany items/histories` + casts address/transaction_info |
| OrderItem | Product(withTrashed), ProductVariant(withTrashed) | `belongsTo` — soft-deleted product همچنان نمایش |
| OrderHistory | Order, OrderShippingStatus | `belongsTo` |
| Bank | — | `bank_type: zarinPal/card_to_card`, `config JSON MerchantId`, `card_number/holder/description` |
| ShippingMethod | — | `freight_balance` (پس کرایه) used in Order |
| Discount/Coupon | — | `discount_id` + `session coupon` + `recordUsage` |

## Calls — Who calls whom

```
CheckoutController::create
  -> Bank::find(bank_id)                    // check card_to_card
  -> Address::with(state,city)->find        // build orderData
  -> ShippingMethod::find
  -> CartService::subtotal/discount/total/toArray
  -> OrderService::create(cartItems, orderData, userDescription)
       -> Order::create
       -> OrderItem::create (loop)
       -> StockService::reserveStock(order)  // DB::transaction lockForUpdate
       -> AuditService::orderCreated
       -> NotificationService::notifyUser+notifyAdmins
  -> OrderService::checkout(order)
       -> Bank::find
       -> BankPaymentService::handlePayment OR handleFreeOrder OR pending_confirmation
  -> CouponService::recordUsage
  -> CartService::clear + session forget

OrderService::callback(field,value,data)
  -> Order::findByField(field,value)        // JSON where
  -> BankPaymentService::handleVerify -> ZarinPal::verifyTransaction (Http)
  -> handleSuccessOrderPayment (update paid/deposit_paid + Audit + Notification)
  -> handleFailedPayment (update unpaid + restoreStock)

Admin::OrderController::changeOrderStatus
  -> OrderService::cancelOrder (update cancelled + restoreStock + Audit + notify)
  -> [Observer::updated fires -> second restoreStock — double bug]

CancelExpiredOrders (cron)
  -> Order::where(paying, created_at<cutoff)
  -> order.update(cancelled) + StockService::restoreStock
  -> [Observer fires again]
```

## Upstream / Downstream

- **Upstream (Orders needs):** Auth (user_id), Cart (items), Address/State/City (snapshot), ShippingMethod/ShippingCalculator, Bank (gateway config), Discount/Coupon, Product/Variant stock.
- **Downstream (needs Orders):** Dashboard (`dashboard.orders/order-detail/factor/receipt`), Admin audit-logs (`audit-logs/order/{orderId}`), Notifications (admin badge newOrdersCount), Factor/bulk-factor print views.

## External & Config

| External | Usage |
|----------|-------|
| `https://payment.zarinpal.com/pg/v4/payment/request.json` | ZarinPal getBankToken |
| `https://payment.zarinpal.com/pg/v4/payment/verify.json` | ZarinPal verifyTransaction |
| `https://payment.zarinpal.com/pg/StartPay/{Authority}` | redirect |
| `config('app.name')` | siteName passed to gateway |
| `route('checkout.finish.zarin-pal')` | callback_url |

- Helpers: `jalali_to_gregorian` (admin filters), `number_format(payment_price)` (notifications), `route()` helpers everywhere.

## Evidence

- `app/Services/OrderService.php:10-16 constructor BankPaymentService+StockService`, `38-120 create`, `122-165 checkout`, `167-250 callback/cancel`, `AuditService`/`NotificationService` try/catch
- `app/Services/StockService.php:24-200 reserve/restore/validate/getAvailable lockForUpdate + InventoryTransaction + Audit`
- `app/Services/BankPaymentService.php:12-95 handlePayment/handleVerify + Auth::loginUsingId + ob_start redirect`
- `app/Payments/ZarinPal.php:20 rial x10, 38 code 100, 75 code 100||101`, `PaymentGatewayFactory:match zarinPal/card_to_card`
- `app/Observers/OrderObserver.php:12-50 created/updated + StockService`
- `app/Http/Controllers/Site/CheckoutController.php:12 CartService inject, 26-280 all checkout steps + create orderData + success guard + finish callbacks`
- `app/Http/Controllers/Admin/OrderController.php:14 OrderService inject, 26 index filters, 58 changeShippingStatus notify, 85 changeOrderStatus cancelOrder`
- `app/Services/CartService.php:290 mergeGuestCart, 15 isAuth session/DB dual`, `routes/site.php:82-135 site routes`, `routes/admin.php:95-102 admin routes`, `routes/console.php everyFiveMinutes`
- `app/Console/Commands/CancelExpiredOrders.php:16 paying cutoff`
