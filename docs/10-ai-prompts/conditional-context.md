# Conditional Context — What to include per task

When AI works on a module, include Global + that module + workflows + integrations as below.

## Per module requirements
| Task module | Must include |
|-------------|--------------|
| Auth/OTP | OtpService, Sms, User, OtpController, auth-otp workflow, throttle |
| Products | Product, Category, Brand, Specification, Variant, Image, Property, Association, ProductDTO, Filterable, StockService |
| Cart | Cart model, CartService, StockService, Product/Variant, cart-checkout workflow |
| Orders | Order, OrderItem, OrderHistory, OrderShippingStatus, OrderService, StockService, PaymentGatewayFactory, Bank, ShippingMethod, Address, Discount, order-lifecycle workflow |
| Payments | Bank, BankPaymentService, PaymentGateway, ZarinPal, Orders, payment-zarinpal + card-to-card workflows |
| Shipping | State, City, Address, ShippingMethod, ShippingCalculator, ChaparShipment, address service, chapar integration (mark BROKEN) |
| Discounts | Discount, Discountable, DiscountUsage, CouponService, WelcomeDiscountService, CartService |
| Inventory | StockService, Product, ProductVariant, InventoryTransaction, AuditService, inventory-reserve-restore workflow |
| Reviews | Review, ReviewVote, ProductController review methods |
| Blog | Blog, BlogCategory, BlogDTO, BlogService |
| Content/CMS | HeroSlide (string linkable_id!), PromoBanner, TrustBadge, DTOs |
| Localization | Language, LocaleService, SetLocale middleware, lakes config, rtl/ltr |
| Settings | Setting, SettingService (rememberForever), settings config tabs, ImageHandler |
| Notifications | Notification, NotificationService, Composers |
| Audit | AuditLog, AuditService, RequestLog, LogAdminRequests |
| Wishlist/RecentlyViewed | WishlistService, RecentlyViewedService (singleton, session) |
| Contact | ContactRequest, PageController |
| Dashboard | DashboardService, Order scopes |
| Search | Filterable trait, Product search, HeroSlideSearchController |

## Workflow inclusion
- Cart/Checkout task -> cart-checkout workflow + auth-otp if guest
- Payment task -> payment-* + order-lifecycle
- Order status change -> order-lifecycle + inventory-reserve-restore
- Inventory -> inventory-reserve-restore
- Coupon -> discount-apply

## Integration inclusion
- OTP/SMS tasks -> kavenegar integration
- Shipping/chapar -> chapar integration
- Upload -> storage integration

## Global always
`10-ai-prompts/global-context.md` + `03-modules/_shared/dependencies.md`
