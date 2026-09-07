# Payment Card-to-Card Workflow

> **Source:** `app/Services/OrderService.php:143-147`, `app/Models/Bank.php`, `app/Models/Order.php`, `app/Http/Controllers/Site/CheckoutController.php:create/success`

## Overview
No gateway. User transfers manually to card number shown, then enters tracking code.

## Bank model
- `bank_type = 'card_to_card'`
- fields: `card_number, card_holder_name, card_description, icon, title`
- config not used

## Flow
```
CheckoutController::create:
  bank = Bank::find(bankId)
  isCardToCard = bank->bank_type === 'card_to_card'
  receiptTrackingCode = input receipt_tracking_code (nullable?)
  receiptPayerCard = input receipt_payer_card
  orderData[...] receipt_tracking_code = isCardToCard ? code : null, receipt_payer_card similarly

OrderService::create -> paying -> reserveStock
OrderService::checkout:
  if bank_type card_to_card:
    order->update(['order_status'=>'pending_confirmation'])
    redirect checkout.success

CheckoutController::success(Order $order):
  if order->user_id !== auth.id -> redirect /
  if pending_confirmation -> redirect / with success "بعد از تایید ادمین ارسال خواهد شد"
  if not paid -> error contact support
  else view checkout.success

Admin: OrderController::changeOrderStatus can set to paid/pending_confirmation/wait_for_verification/cancelled manually without validation.
Order::getBankTrackingCodeAttribute: if card_to_card return receipt_tracking_code else RefID/TraceNo
```

## Migration
`2026_08_29 add payment_receipt` -> `2026_08_31 replace with receipt_tracking_code + receipt_payer_card` — payment_receipt dropped.

## AI gotcha
- Card info stored plain text; RequestLog logs it (security).
- No verification logic for card-to-card — manual admin confirm.
- pending_confirmation not in old migration comment but real.
