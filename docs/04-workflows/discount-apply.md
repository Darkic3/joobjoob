# Discount / Coupon Workflow

> **Source:** `app/Models/Discount.php`, `CouponService.php`, `DiscountController.php`, `CartService.php:discount()`

## Model
- `discounts: title, code unique, amount, type percent/fixed (also cash legacy), count, basket_minimum_price, first_purchase, with_discount, user_id, max_usage_per_user, pay_type category|brand|product, is_active, starts_at/expires_at, softDeletes`
- Pivot `discountable` morphs to Product/Category/Brand
- Usage: `discount_usage (discount_id,user_id,order_id,discount_amount,used_at)` + legacy `discount_users`

## CouponService::apply(code, subtotal)
1. upper trim, find Discount where code
2. isCurrentlyValid: is_active && starts_at <= now <= expires_at
3. basket_minimum_price check
4. first_purchase: if true && user has paid orders -> reject
5. user_id restriction
6. hasReachedLimit: count (total) + max_usage_per_user per user via usageRecords count
7. calculateDiscount(subtotal) -> percent => subtotal*amount/100 else min(amount, subtotal)
8. session coupon = {code,discount_id,type,value,discount_amount,title,with_discount,pay_type}

## Eligibility per item
`Discount::isEligibleCartItem(item)` checks pay_type:
- category -> product.product_category_id in discount.categories()
- brand -> product.brand_id in discount.brands()
- product -> product.id in discount.products()
- else true

`appliesToDiscountedItems() = (bool)with_discount` — if false, skip discounted products (original_price > price) and skip ineligible items.

`CouponService::calculateForCart(cart)`:
- if no pay_type && with_discount -> simple calculate(subtotal)
- else loop items, skip discounted if not allowed, skip ineligible, sum calculateDiscount(itemTotal), min(totalDiscount, subtotal)

## Order integration
CheckoutController::create discount_id = session coupon.discount_id, discount_price = cart.discount()
After OrderService::checkout success, if discount_id && couponSession -> recordUsage(discount_id, user_id, order_id, discount_amount)

## AI gotcha
- code unique after enhance_discounts migration (DISC-0001 generation).
- pay_type nullable — if empty means all products.
- with_discount boolean controls stacking on already discounted items.
