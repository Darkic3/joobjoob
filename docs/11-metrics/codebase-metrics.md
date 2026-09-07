# Codebase Metrics — Verified

> **Source:** find/grep counts on 2026-09-04, Phase 1.5 verified

| Category | Count | Details |
|----------|-------|---------|
| Controllers | 37 | Admin 25 (AdminAuth,AuditLog,Bank,BlogCategory,Blog,Brand,Ckeditor,Contact,Dashboard,Discount,HeroSlide,HeroSlideSearch,Language,Notification,Order,ProductCategory,Product,PromoBanner,Review,Role,Setting,ShippingMethod,Specification,TrustBadge,User) + Site 11 (Blog,Cart,Checkout,Dashboard,Home,Notification,Otp,Page,Product,SiteAuth,Wishlist) + base |
| Models | 40 | includes Address,AuditLog,Bank,Blog,BlogCategory,Brand,Cart,City,ContactRequest,Discount,Discountable,DiscountUsage,HeroSlide,InventoryTransaction,Language,Notification,Order,OrderHistory,OrderItem,OrderShippingStatus,Permission,Product,ProductAssociation,ProductCategory,ProductImage,ProductSpecification,ProductVariant,Review,ReviewVote,Role,Setting,ShippingMethod,Specification,State,TrustBadge,User etc |
| Services | 26 | AddressService, AuditService, BankPaymentService, CartService, ChaparShipment, CityService, CouponService, DashboardService, LocaleService, NotificationService, OrderService, OtpService, SettingService, ShippingCalculator, StateService, StockService, WelcomeDiscountService, WishlistService, RecentlyViewedService, etc |
| DTOs | 9 | ProductDTO, HeroSlideDTO, BrandDTO, BlogDTO, BlogCategoryDTO, ProductCategoryDTO, SpecificationDTO, PromoBannerDTO, TrustBadgeDTO |
| Requests | 17 | Admin 15 + Site 2 (CardToCardCheckoutRequest, UpdateReceiptRequest) |
| Middleware | 4 | SetLocale, SetTheme, AdminPermission, LogAdminRequests |
| Traits | 5+1 | Filterable, HasSlug, HasImageAttributes, HasJalaliDate, HasMetaData + HasAdminPermissions |
| Helpers | 16 files | Sms, ImageHandler, ImageUploader, ImageResizer, Uploader, Video*, Helper, MetaManager, NumberHelper, locale, jdate, etc |
| Migrations | 73 | 0001..2026_09_03, 38+ tables + 3 system |
| Seeders | 25 | |
| Config | 16 | admin,app,auth,cache,database,filesystems,kavenegar,languages,permissions,settings,paths etc |
| Routes lines | 292 | site.php 171 + admin.php 121 + web.php wrapper |
| Blade views | 184 | admin/*, site/*, checkout/*, components/*, layouts/* |
| Tests | 45 | feature + unit |

| Missing layers | Count | Note |
|----------------|-------|------|
| Events/Listeners/Jobs/Policies/Resources | 0 | Synchronous only, Observer for Order |

## Growth notes
- Migrations churn: payment_receipt -> receipt_tracking_code, enhance_discounts added code unique.
- DTO only for complex Admin forms.

## Debt summary
See 07-security/threats.md for full debt table. Top: N+1 CartService, God OrderService, duplicated $siteRoutes, Sms SSL off, missing index, Observer bug.
