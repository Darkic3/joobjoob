# Routes Dump — generated via `php artisan route:list --json`
> Generated: 2026-09-06 | 273 app routes (excluding vendor). Machine-generated: source of truth for all `api-contract.md` files.

## Admin\AdminAuthController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/login` | `admin.login` | web | `showLogin` |
| POST | `admin/login` | `admin.login.submit` | web, throttle:5,1 | `postLogin` |
| POST | `admin/logout` | `admin.logout` | web, auth, adminPermission, log.admin, refreshRememberMe | `logout` |

## Admin\AuditLogController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/audit-logs` | `admin.audit-logs.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| GET|HEAD | `admin/audit-logs/order/{orderId}` | `admin.audit-logs.order-history` | web, auth, adminPermission, log.admin, refreshRememberMe | `orderHistory` |
| GET|HEAD | `admin/audit-logs/product/{productId}` | `admin.audit-logs.product-history` | web, auth, adminPermission, log.admin, refreshRememberMe | `productHistory` |
| GET|HEAD | `admin/audit-logs/{id}` | `admin.audit-logs.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |

## Admin\BankController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/banks` | `admin.banks.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/banks` | `admin.banks.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/banks/create` | `admin.banks.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| DELETE | `admin/banks/{bank}` | `admin.banks.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/banks/{bank}` | `admin.banks.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/banks/{bank}` | `admin.banks.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/banks/{bank}/edit` | `admin.banks.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |
| POST | `admin/banks/{bank}/toggle-status` | `admin.banks.toggle-status` | web, auth, adminPermission, log.admin, refreshRememberMe | `toggleStatus` |

## Admin\BlogCategoryController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/blog-categories` | `admin.blog-categories.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/blog-categories` | `admin.blog-categories.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/blog-categories/create` | `admin.blog-categories.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| DELETE | `admin/blog-categories/{blog_category}` | `admin.blog-categories.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/blog-categories/{blog_category}` | `admin.blog-categories.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/blog-categories/{blog_category}` | `admin.blog-categories.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/blog-categories/{blog_category}/edit` | `admin.blog-categories.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\BlogController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/blogs` | `admin.blogs.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/blogs` | `admin.blogs.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/blogs/create` | `admin.blogs.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| DELETE | `admin/blogs/{blog}` | `admin.blogs.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/blogs/{blog}` | `admin.blogs.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/blogs/{blog}` | `admin.blogs.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/blogs/{blog}/edit` | `admin.blogs.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\BrandController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/brands` | `admin.brands.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/brands` | `admin.brands.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/brands/create` | `admin.brands.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| POST | `admin/brands/reorder` | `admin.brands.reorder` | web, auth, adminPermission, log.admin, refreshRememberMe | `reorder` |
| DELETE | `admin/brands/{brand}` | `admin.brands.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/brands/{brand}` | `admin.brands.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/brands/{brand}` | `admin.brands.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/brands/{brand}/edit` | `admin.brands.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\CkeditorController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| POST | `admin/ckeditor/upload` | `admin.ckeditor.upload` | web, auth, adminPermission, log.admin, refreshRememberMe | `upload` |

## Admin\ContactController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/contact` | `admin.contact.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| DELETE | `admin/contact/{contact}` | `admin.contact.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/contact/{contact}` | `admin.contact.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |

## Admin\DashboardController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/dashboard` | `admin.dashboard.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |

## Admin\DiscountController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/discounts` | `admin.discounts.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/discounts` | `admin.discounts.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/discounts/create` | `admin.discounts.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| GET|HEAD | `admin/discounts/users/filter` | `admin.discounts.users.filter` | web, auth, adminPermission, log.admin, refreshRememberMe | `filterUsers` |
| DELETE | `admin/discounts/{discount}` | `admin.discounts.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/discounts/{discount}` | `admin.discounts.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/discounts/{discount}` | `admin.discounts.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/discounts/{discount}/edit` | `admin.discounts.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |
| POST | `admin/discounts/{discount}/toggle-status` | `admin.discounts.toggle-status` | web, auth, adminPermission, log.admin, refreshRememberMe | `toggleStatus` |

## Admin\HeroSlideController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/hero-slides` | `admin.hero-slides.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/hero-slides` | `admin.hero-slides.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/hero-slides/create` | `admin.hero-slides.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| POST | `admin/hero-slides/reorder` | `admin.hero-slides.reorder` | web, auth, adminPermission, log.admin, refreshRememberMe | `reorder` |
| DELETE | `admin/hero-slides/{hero_slide}` | `admin.hero-slides.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| PUT|PATCH | `admin/hero-slides/{hero_slide}` | `admin.hero-slides.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/hero-slides/{hero_slide}/edit` | `admin.hero-slides.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\HeroSlideSearchController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/hero-slides/search-model` | `admin.hero-slides.search-model` | web, auth, adminPermission, log.admin, refreshRememberMe | `searchModel` |

## Admin\LanguageController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/languages` | `admin.languages.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/languages` | `admin.languages.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/languages/create` | `admin.languages.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| POST | `admin/languages/reorder` | `admin.languages.reorder` | web, auth, adminPermission, log.admin, refreshRememberMe | `reorder` |
| DELETE | `admin/languages/{language}` | `admin.languages.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| PUT|PATCH | `admin/languages/{language}` | `admin.languages.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/languages/{language}/edit` | `admin.languages.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |
| PATCH | `admin/languages/{language}/make-default` | `admin.languages.make-default` | web, auth, adminPermission, log.admin, refreshRememberMe | `makeDefault` |
| PATCH | `admin/languages/{language}/toggle` | `admin.languages.toggle` | web, auth, adminPermission, log.admin, refreshRememberMe | `toggle` |

## Admin\NotificationController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| POST | `admin/api/notifications/read-all` | `admin.api.notifications.mark-all-read` | web, auth, adminPermission, log.admin, refreshRememberMe | `markAllRead` |
| GET|HEAD | `admin/api/notifications/unread-count` | `admin.api.notifications.unread-count` | web, auth, adminPermission, log.admin, refreshRememberMe | `unreadCount` |
| POST | `admin/api/notifications/{id}/read` | `admin.api.notifications.mark-read` | web, auth, adminPermission, log.admin, refreshRememberMe | `markRead` |
| GET|HEAD | `admin/notifications` | `admin.notifications.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| GET|HEAD | `admin/notifications/{id}` | `admin.notifications.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| GET|HEAD | `admin/notifications/{id}/go` | `admin.notifications.go` | web, auth, adminPermission, log.admin, refreshRememberMe | `go` |

## Admin\OrderController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/orders` | `admin.orders.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| GET|HEAD | `admin/orders/bulk-factor` | `admin.orders.bulk-factor` | web, auth, adminPermission, log.admin, refreshRememberMe | `bulkFactor` |
| POST | `admin/orders/change-order-status/{id}` | `admin.orders.change-order-status` | web, auth, adminPermission, log.admin, refreshRememberMe | `changeOrderStatus` |
| POST | `admin/orders/post-code` | `admin.orders.post-code` | web, auth, adminPermission, log.admin, refreshRememberMe | `postCode` |
| GET|HEAD | `admin/orders/{id}` | `admin.orders.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| POST | `admin/orders/{id}/change-shipping-status` | `admin.orders.change-shipping-status` | web, auth, adminPermission, log.admin, refreshRememberMe | `changeShippingStatus` |
| GET|HEAD | `admin/orders/{id}/factor` | `admin.orders.factor` | web, auth, adminPermission, log.admin, refreshRememberMe | `factor` |

## Admin\ProductCategoryController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/product-categories` | `admin.product-categories.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/product-categories` | `admin.product-categories.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/product-categories/create` | `admin.product-categories.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| DELETE | `admin/product-categories/{product_category}` | `admin.product-categories.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| PUT|PATCH | `admin/product-categories/{product_category}` | `admin.product-categories.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/product-categories/{product_category}/edit` | `admin.product-categories.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\ProductController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/products` | `admin.products.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/products` | `admin.products.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/products/create` | `admin.products.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| DELETE | `admin/products/{product}` | `admin.products.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/products/{product}` | `admin.products.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/products/{product}` | `admin.products.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/products/{product}/edit` | `admin.products.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\PromoBannerController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/promo-banners` | `admin.promo-banners.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/promo-banners` | `admin.promo-banners.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/promo-banners/create` | `admin.promo-banners.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| POST | `admin/promo-banners/reorder` | `admin.promo-banners.reorder` | web, auth, adminPermission, log.admin, refreshRememberMe | `reorder` |
| DELETE | `admin/promo-banners/{promo_banner}` | `admin.promo-banners.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| PUT|PATCH | `admin/promo-banners/{promo_banner}` | `admin.promo-banners.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/promo-banners/{promo_banner}/edit` | `admin.promo-banners.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\ReviewController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/reviews` | `admin.reviews.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/reviews` | `admin.reviews.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| PATCH | `admin/reviews/approval` | `admin.reviews.approval` | web, auth, adminPermission, log.admin, refreshRememberMe | `updateApproval` |
| PATCH | `admin/reviews/featured` | `admin.reviews.featured` | web, auth, adminPermission, log.admin, refreshRememberMe | `toggleFeatured` |
| POST | `admin/reviews/reply` | `admin.reviews.reply` | web, auth, adminPermission, log.admin, refreshRememberMe | `reply` |
| DELETE | `admin/reviews/{review}` | `admin.reviews.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/reviews/{review}` | `admin.reviews.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/reviews/{review}` | `admin.reviews.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |

## Admin\RoleController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/roles` | `admin.roles.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/roles` | `admin.roles.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/roles/create` | `admin.roles.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| DELETE | `admin/roles/{role}` | `admin.roles.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| PUT|PATCH | `admin/roles/{role}` | `admin.roles.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/roles/{role}/edit` | `admin.roles.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\SettingController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/settings/{group?}` | `admin.settings.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |
| PUT | `admin/settings/{group}` | `admin.settings.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |

## Admin\ShippingMethodController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/shipping-methods` | `admin.shipping-methods.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/shipping-methods` | `admin.shipping-methods.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/shipping-methods/create` | `admin.shipping-methods.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| POST | `admin/shipping-methods/{shippingMethod}/toggle-status` | `admin.shipping-methods.toggle-status` | web, auth, adminPermission, log.admin, refreshRememberMe | `toggleStatus` |
| DELETE | `admin/shipping-methods/{shipping_method}` | `admin.shipping-methods.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/shipping-methods/{shipping_method}` | `admin.shipping-methods.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/shipping-methods/{shipping_method}` | `admin.shipping-methods.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/shipping-methods/{shipping_method}/edit` | `admin.shipping-methods.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\SpecificationController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/specifications` | `admin.specifications.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/specifications` | `admin.specifications.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/specifications/create` | `admin.specifications.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| DELETE | `admin/specifications/{specification}` | `admin.specifications.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/specifications/{specification}` | `admin.specifications.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/specifications/{specification}` | `admin.specifications.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/specifications/{specification}/edit` | `admin.specifications.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\TrustBadgeController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/trust-badges` | `admin.trust-badges.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/trust-badges` | `admin.trust-badges.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/trust-badges/create` | `admin.trust-badges.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| POST | `admin/trust-badges/reorder` | `admin.trust-badges.reorder` | web, auth, adminPermission, log.admin, refreshRememberMe | `reorder` |
| DELETE | `admin/trust-badges/{trust_badge}` | `admin.trust-badges.destroy` | web, auth, adminPermission, log.admin, refreshRememberMe | `destroy` |
| GET|HEAD | `admin/trust-badges/{trust_badge}` | `admin.trust-badges.show` | web, auth, adminPermission, log.admin, refreshRememberMe | `show` |
| PUT|PATCH | `admin/trust-badges/{trust_badge}` | `admin.trust-badges.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/trust-badges/{trust_badge}/edit` | `admin.trust-badges.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Admin\UserController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `admin/users` | `admin.users.index` | web, auth, adminPermission, log.admin, refreshRememberMe | `index` |
| POST | `admin/users` | `admin.users.store` | web, auth, adminPermission, log.admin, refreshRememberMe | `store` |
| GET|HEAD | `admin/users/create` | `admin.users.create` | web, auth, adminPermission, log.admin, refreshRememberMe | `create` |
| PUT|PATCH | `admin/users/{user}` | `admin.users.update` | web, auth, adminPermission, log.admin, refreshRememberMe | `update` |
| GET|HEAD | `admin/users/{user}/edit` | `admin.users.edit` | web, auth, adminPermission, log.admin, refreshRememberMe | `edit` |

## Site\BlogController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `blog-categories/{category}` | `blogs.category` | web | `category` |
| GET|HEAD | `blogs` | `blogs.index` | web | `index` |
| GET|HEAD | `blogs/{blog}` | `blogs.show` | web | `show` |
| GET|HEAD | `{locale}/blog-categories/{category}` | `locale.blogs.category` | web | `category` |
| GET|HEAD | `{locale}/blogs` | `locale.blogs.index` | web | `index` |
| GET|HEAD | `{locale}/blogs/{blog}` | `locale.blogs.show` | web | `show` |

## Site\CartController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `cart` | `cart.index` | web | `index` |
| POST | `cart/add/{product}` | `cart.add` | web | `add` |
| POST | `cart/coupon` | `cart.coupon` | web, auth | `applyCoupon` |
| DELETE | `cart/remove/{rowId}` | `cart.remove` | web | `remove` |
| PATCH | `cart/update/{rowId}` | `cart.update` | web | `update` |
| GET|HEAD | `{locale}/cart` | `locale.cart.index` | web | `index` |
| POST | `{locale}/cart/add/{product}` | `locale.cart.add` | web | `add` |
| POST | `{locale}/cart/coupon` | `locale.cart.coupon` | web, auth | `applyCoupon` |
| DELETE | `{locale}/cart/remove/{rowId}` | `locale.cart.remove` | web | `remove` |
| PATCH | `{locale}/cart/update/{rowId}` | `locale.cart.update` | web | `update` |

## Site\CheckoutController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `checkout` | `checkout.index` | web, auth | `index` |
| GET|HEAD | `checkout/address` | `checkout.address` | web, auth | `address` |
| GET|HEAD | `checkout/address-price` | `checkout.address-price` | web, auth | `addressPrice` |
| POST | `checkout/apply-discount` | `checkout.apply-discount` | web, auth | `applyDiscount` |
| GET|HEAD | `checkout/cities` | `checkout.cities` | web, auth | `cities` |
| POST | `checkout/create` | `checkout.create` | web, auth | `create` |
| GET|HEAD | `checkout/failed` | `checkout.failed` | web, auth | `failed` |
| GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | `checkout/finish` | `checkout.finish.zarin-pal` | web | `finishZarinPal` |
| GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | `checkout/finish-sadad` | `checkout.finish.sadad-bank` | web | `finishSadad` |
| GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | `checkout/finish-saman` | `checkout.finish.saman-bank` | web | `finishSaman` |
| GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | `checkout/finish-zibal` | `checkout.finish.zibal-bank` | web | `finishZibal` |
| GET|HEAD | `checkout/order-price` | `checkout.order-price` | web, auth | `orderPrice` |
| GET|HEAD | `checkout/payment` | `checkout.payment` | web, auth | `payment` |
| POST | `checkout/remove-discount` | `checkout.remove-discount` | web, auth | `removeDiscount` |
| POST | `checkout/set-shipment` | `checkout.set-shipment` | web, auth | `setShipment` |
| POST | `checkout/shipments` | `checkout.shipments` | web, auth | `shipments` |
| GET|HEAD | `checkout/success/{order}` | `checkout.success` | web, auth | `success` |
| POST | `checkout/validate-stock` | `checkout.validate-stock` | web, auth | `validateStock` |
| GET|HEAD | `{locale}/checkout` | `locale.checkout.index` | web, auth | `index` |
| GET|HEAD | `{locale}/checkout/address` | `locale.checkout.address` | web, auth | `address` |
| GET|HEAD | `{locale}/checkout/address-price` | `locale.checkout.address-price` | web, auth | `addressPrice` |
| POST | `{locale}/checkout/apply-discount` | `locale.checkout.apply-discount` | web, auth | `applyDiscount` |
| GET|HEAD | `{locale}/checkout/cities` | `locale.checkout.cities` | web, auth | `cities` |
| POST | `{locale}/checkout/create` | `locale.checkout.create` | web, auth | `create` |
| GET|HEAD | `{locale}/checkout/failed` | `locale.checkout.failed` | web, auth | `failed` |
| GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | `{locale}/checkout/finish` | `locale.checkout.finish.zarin-pal` | web | `finishZarinPal` |
| GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | `{locale}/checkout/finish-sadad` | `locale.checkout.finish.sadad-bank` | web | `finishSadad` |
| GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | `{locale}/checkout/finish-saman` | `locale.checkout.finish.saman-bank` | web | `finishSaman` |
| GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | `{locale}/checkout/finish-zibal` | `locale.checkout.finish.zibal-bank` | web | `finishZibal` |
| GET|HEAD | `{locale}/checkout/order-price` | `locale.checkout.order-price` | web, auth | `orderPrice` |
| GET|HEAD | `{locale}/checkout/payment` | `locale.checkout.payment` | web, auth | `payment` |
| POST | `{locale}/checkout/remove-discount` | `locale.checkout.remove-discount` | web, auth | `removeDiscount` |
| POST | `{locale}/checkout/set-shipment` | `locale.checkout.set-shipment` | web, auth | `setShipment` |
| POST | `{locale}/checkout/shipments` | `locale.checkout.shipments` | web, auth | `shipments` |
| GET|HEAD | `{locale}/checkout/success/{order}` | `locale.checkout.success` | web, auth | `success` |
| POST | `{locale}/checkout/validate-stock` | `locale.checkout.validate-stock` | web, auth | `validateStock` |

## Site\DashboardController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `account` | `dashboard` | web, auth | `index` |
| GET|HEAD | `account/addresses` | `dashboard.addresses` | web, auth | `addresses` |
| POST | `account/addresses` | `dashboard.addresses.create` | web, auth | `createAddress` |
| GET|HEAD | `account/addresses/cities` | `dashboard.addresses.cities` | web, auth | `cities` |
| DELETE | `account/addresses/{id}` | `dashboard.addresses.delete` | web, auth | `deleteAddress` |
| PUT | `account/addresses/{id}` | `dashboard.addresses.update` | web, auth | `updateAddress` |
| GET|HEAD | `account/orders` | `dashboard.orders` | web, auth | `orders` |
| GET|HEAD | `account/orders/{id}` | `dashboard.order-detail` | web, auth | `orderDetail` |
| GET|HEAD | `account/orders/{id}/factor` | `dashboard.order-factor` | web, auth | `orderFactor` |
| PATCH | `account/orders/{id}/receipt` | `dashboard.order-receipt.update` | web, auth | `updateReceipt` |
| GET|HEAD | `account/wishlist` | `dashboard.wishlist` | web, auth | `wishlist` |
| GET|HEAD | `{locale}/account` | `locale.dashboard` | web, auth | `index` |
| GET|HEAD | `{locale}/account/addresses` | `locale.dashboard.addresses` | web, auth | `addresses` |
| POST | `{locale}/account/addresses` | `locale.dashboard.addresses.create` | web, auth | `createAddress` |
| GET|HEAD | `{locale}/account/addresses/cities` | `locale.dashboard.addresses.cities` | web, auth | `cities` |
| DELETE | `{locale}/account/addresses/{id}` | `locale.dashboard.addresses.delete` | web, auth | `deleteAddress` |
| PUT | `{locale}/account/addresses/{id}` | `locale.dashboard.addresses.update` | web, auth | `updateAddress` |
| GET|HEAD | `{locale}/account/orders` | `locale.dashboard.orders` | web, auth | `orders` |
| GET|HEAD | `{locale}/account/orders/{id}` | `locale.dashboard.order-detail` | web, auth | `orderDetail` |
| GET|HEAD | `{locale}/account/orders/{id}/factor` | `locale.dashboard.order-factor` | web, auth | `orderFactor` |
| PATCH | `{locale}/account/orders/{id}/receipt` | `locale.dashboard.order-receipt.update` | web, auth | `updateReceipt` |
| GET|HEAD | `{locale}/account/wishlist` | `locale.dashboard.wishlist` | web, auth | `wishlist` |

## Site\HomeController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `/` | `home` | web | `index` |
| GET|HEAD | `seed` | `seed` | web | `seed` |
| GET|HEAD | `{locale}` | `locale.home` | web | `index` |
| GET|HEAD | `{locale}/seed` | `locale.seed` | web | `seed` |

## Site\NotificationController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `account/notifications` | `dashboard.notifications` | web, auth | `index` |
| GET|HEAD | `account/notifications/{id}` | `dashboard.notification-detail` | web, auth | `show` |
| POST | `api/notifications/read-all` | `api.notifications.mark-all-read` | web, auth | `markAllRead` |
| GET|HEAD | `api/notifications/unread-count` | `api.notifications.unread-count` | web, auth | `unreadCount` |
| POST | `api/notifications/{id}/read` | `api.notifications.mark-read` | web, auth | `markRead` |
| GET|HEAD | `{locale}/account/notifications` | `locale.dashboard.notifications` | web, auth | `index` |
| GET|HEAD | `{locale}/account/notifications/{id}` | `locale.dashboard.notification-detail` | web, auth | `show` |
| POST | `{locale}/api/notifications/read-all` | `locale.api.notifications.mark-all-read` | web, auth | `markAllRead` |
| GET|HEAD | `{locale}/api/notifications/unread-count` | `locale.api.notifications.unread-count` | web, auth | `unreadCount` |
| POST | `{locale}/api/notifications/{id}/read` | `locale.api.notifications.mark-read` | web, auth | `markRead` |

## Site\OtpController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| POST | `login/check-phone` | `login.otp.check` | web | `checkPhone` |
| GET|HEAD | `login/otp` | `login.otp` | web | `show` |
| POST | `login/otp/resend` | `login.otp.resend` | web, throttle:3,1 | `resend` |
| POST | `login/otp/verify` | `login.otp.verify` | web, throttle:5,1 | `verify` |
| POST | `login/send` | `login.otp.send` | web, throttle:5,1 | `send` |
| GET|HEAD | `login/verify` | `login.verify` | web | `showVerify` |
| POST | `{locale}/login/check-phone` | `locale.login.otp.check` | web | `checkPhone` |
| GET|HEAD | `{locale}/login/otp` | `locale.login.otp` | web | `show` |
| POST | `{locale}/login/otp/resend` | `locale.login.otp.resend` | web, throttle:3,1 | `resend` |
| POST | `{locale}/login/otp/verify` | `locale.login.otp.verify` | web, throttle:5,1 | `verify` |
| POST | `{locale}/login/send` | `locale.login.otp.send` | web, throttle:5,1 | `send` |
| GET|HEAD | `{locale}/login/verify` | `locale.login.verify` | web | `showVerify` |

## Site\PageController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| POST | `contact` | `site.contact.send` | web, throttle:5,1 | `sendContact` |
| GET|HEAD | `page/{page}` | `site.page` | web | `show` |
| POST | `{locale}/contact` | `locale.site.contact.send` | web, throttle:5,1 | `sendContact` |
| GET|HEAD | `{locale}/page/{page}` | `locale.site.page` | web | `show` |

## Site\ProductController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `product-categories/{category}` | `products.category` | web | `category` |
| GET|HEAD | `products` | `products.index` | web | `index` |
| GET|HEAD | `products/{product}` | `products.show` | web | `show` |
| GET|HEAD | `products/{product}/quick-view` | `products.quick-view` | web | `quickView` |
| POST | `products/{product}/reviews` | `products.reviews.store` | web | `storeReview` |
| POST | `products/{product}/reviews/{review}/reply` | `products.reviews.reply` | web | `storeReply` |
| POST | `products/{product}/reviews/{review}/vote` | `products.reviews.vote` | web, auth | `voteReview` |
| GET|HEAD | `{locale}/product-categories/{category}` | `locale.products.category` | web | `category` |
| GET|HEAD | `{locale}/products` | `locale.products.index` | web | `index` |
| GET|HEAD | `{locale}/products/{product}` | `locale.products.show` | web | `show` |
| GET|HEAD | `{locale}/products/{product}/quick-view` | `locale.products.quick-view` | web | `quickView` |
| POST | `{locale}/products/{product}/reviews` | `locale.products.reviews.store` | web | `storeReview` |
| POST | `{locale}/products/{product}/reviews/{review}/reply` | `locale.products.reviews.reply` | web | `storeReply` |
| POST | `{locale}/products/{product}/reviews/{review}/vote` | `locale.products.reviews.vote` | web, auth | `voteReview` |

## Site\SiteAuthController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `login` | `login` | web | `showLogin` |
| POST | `login` | `login.submit` | web | `login` |
| POST | `logout` | `logout` | web, auth | `logout` |
| GET|HEAD | `{locale}/login` | `locale.login` | web | `showLogin` |
| POST | `{locale}/login` | `locale.login.submit` | web | `login` |
| POST | `{locale}/logout` | `locale.logout` | web, auth | `logout` |

## Site\WishlistController
| Method | URI | Name | Middleware | Action |
|---|---|---|---|---|
| GET|HEAD | `wishlist` | `wishlist.index` | web | `index` |
| POST | `wishlist/toggle/{product}` | `wishlist.toggle` | web | `toggle` |
| GET|HEAD | `{locale}/wishlist` | `locale.wishlist.index` | web | `index` |
| POST | `{locale}/wishlist/toggle/{product}` | `locale.wishlist.toggle` | web | `toggle` |
