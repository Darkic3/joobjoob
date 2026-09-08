# UI/UX & Frontend Audit Report

> **Date:** 2026-09-08
> **Auditor:** Buffy (Module Code Inspector)
> **Scope:** Full frontend stack — Blade templates, CSS, JS, RTL, Accessibility, Performance, Mobile
> **Files Scanned:** 15+ Blade templates, 3 CSS files, 4 JS files, vite.config.js, package.json

---

## A. ✅ قوت‌ها (What's Done Well)

### Design System
1. **Neumorphism design system** — consistent `neu-*` classes (`neu-card`, `neu-btn`, `neu-input`, `neu-toggle`, `neu-flat-*`)
2. **CSS custom properties** — `var(--bg)`, `var(--accent)`, `var(--text-primary)`, `var(--danger)`, `var(--success)` etc.
3. **Dark mode** — `data-theme` attribute on `<html>`, theme toggle with persistence
4. **Glass morphism** — `backdrop-blur-md`, `color-mix(in srgb, ...)` for modern UI

### RTL/LTR Support
5. **Full RTL** — `<html dir="{{ $direction }}">`, Tailwind RTL utilities (`ms-*`, `end-*`, `rtl:rotate-180`)
6. **Font strategy** — Vazirmatn (Persian) + JetBrains Mono (numbers/code)
7. **Locale-aware digits** — `NumberHelper::localizeDigits()`, `window.localizeDigits.convert`
8. **Language switching** — dropdown in header, links in mobile menu, `localizedRoute()` everywhere

### Mobile Experience
9. **Bottom mobile bar** — 4-item nav (Home, Search, Wishlist, Cart) with search sheet
10. **Hamburger menu** — full-screen slide-in with ARIA attributes
11. **Touch-friendly** — minimum 44px touch targets on buttons
12. **Sticky cart bar** — on product show page (mobile)

### Components
13. **17 reusable Blade components** — header, footer, product-card, category-card, etc.
14. **Product card** — wishlist toggle, quick view, discount badge, stock bar, lazy loading
15. **Review system** — star rating, nested replies, social proof toast

### Performance
16. **Vite bundling** — tree-shaking, CSS/JS optimization
17. **Lazy loading** — `loading="lazy"` on images
18. **Scroll animations** — `reveal-on-scroll` with IntersectionObserver
19. **`prefers-reduced-motion`** — all animations disabled for accessibility

### SEO
20. **JSON-LD** — Product schema on show page
21. **Canonical + hreflang** — proper i18n SEO tags
22. **Open Graph** — locale-aware meta tags

---

## B. ⚠️ مشکلات بحرانی (Critical Issues)

| # | مشکل | شدت | فایل | توضیح |
|---|------|------|------|-------|
| 1 | **Double font download** | HIGH | `app.css` + `app.blade.php` | Vazirmatn loaded via `@import url(...)` in CSS AND `<link>` in HTML — ~200KB extra download |
| 2 | **No skip-to-content link** | HIGH | `layouts/site/app.blade.php` | Keyboard users must tab through entire header to reach content |
| 3 | **Product thumbnail alt missing** | HIGH | `show.blade.php:108` | `<img src="..." alt="thumbnail">` — should be `alt="{{ $product->trans('name') }} - {{ $loop->iteration }}"` |
| 4 | **No 404 page** | HIGH | `resources/views/errors/` | No custom 404/500 pages — Laravel default shown |
| 5 | **Inline script in header** | MEDIUM | `header.blade.php` | `notificationBell()` function is inline — should be bundled |

---

## C. 🟡 مشکلات متوسط (Medium Issues)

### RTL Issues
1. **Product show: "تومان" text** — `<span>تومان</span>` after price is not `dir="rtl"` explicitly (may inherit wrong direction in some contexts)
2. **Breadcrumb separator** — hardcoded `/` not RTL-aware (should use `rtl:rotate-180` arrow)
3. **Star rating `★★★★★`** — rendered as text, not semantic `<span>` with `aria-label`

### Accessibility Issues
4. **Form labels** — some forms use `<label>` but not `for="id"` association (e.g., review form)
5. **Color contrast** — `var(--text-secondary)` on `var(--bg)` may fail WCAG AA (need to verify)
6. **Focus indicators** — `neu-btn` has no visible focus ring for keyboard navigation
7. **Modal focus trap** — `quick-view-modal` and `reply-modal` don't trap focus

### Performance Issues
8. **Render-blocking CSS** — `neumorphism.css` loaded via `@import` in CSS (should be bundled)
9. **No `preconnect` for Google Fonts** — adds ~100ms to font loading
10. **No skeleton loading states** — pages show blank while loading
11. **Large inline scripts** — variant selector (~200 lines) is inline in `show.blade.php`

### Mobile Issues
12. **Hero slider height** — may be too tall on 320px screens
13. **Cart step indicator** — 4-step process may be confusing on small screens
14. **Address form** — inline panel toggle may be hard to discover

---

## D. 🔵 پیشنهادات بهبود (Enhancement Suggestions)

### High Priority
1. **Add skeleton loading** — for product cards, cart items, checkout steps
2. **Add `srcset` to product images** — serve smaller images on mobile
3. **Add `<link rel="preload">` for Vazirmatn** — prevent FOIT
4. **Create custom 404/500 pages** — branded error pages with navigation
5. **Add focus-visible styles** — visible focus ring for keyboard users

### Medium Priority
6. **Move inline JS to bundles** — variant selector, lightbox, review form
7. **Add `aria-live` region for toasts** — screen reader announcements
8. **Add loading spinner for AJAX** — coupon apply, shipping methods
9. **Add confirmation dialog for delete** — cart remove, review delete
10. **Add breadcrumbs to all pages** — not just product show

### Low Priority
11. **Add print styles** — for order confirmation page
12. **Add `font-display: swap`** — prevent invisible text
13. **Consider PWA manifest** — installability
14. **Add service worker** — offline support for static pages
15. **Add micro-interactions** — button hover states, card hover effects

---

## E. 📐 Design System Recommendation

### Current State
- ✅ Neumorphism classes exist (`neu-*`)
- ✅ CSS custom properties for colors
- ❌ No documented design tokens
- ❌ No spacing scale documentation
- ❌ No typography scale documentation

### Recommended Design Tokens

```css
:root {
  /* Colors */
  --color-accent: #6366f1;
  --color-success: #22c55e;
  --color-danger: #ef4444;
  --color-warning: #f59e0b;
  --color-gold: #eab308;

  /* Spacing (4px base) */
  --space-1: 0.25rem;  /* 4px */
  --space-2: 0.5rem;   /* 8px */
  --space-3: 0.75rem;  /* 12px */
  --space-4: 1rem;     /* 16px */
  --space-5: 1.25rem;  /* 20px */
  --space-6: 1.5rem;   /* 24px */
  --space-8: 2rem;     /* 32px */

  /* Typography */
  --text-xs: 0.75rem;   /* 12px */
  --text-sm: 0.875rem;  /* 14px */
  --text-base: 1rem;    /* 16px */
  --text-lg: 1.125rem;  /* 18px */
  --text-xl: 1.25rem;   /* 20px */
  --text-2xl: 1.5rem;   /* 24px */
  --text-3xl: 1.875rem; /* 30px */

  /* Border Radius */
  --radius-sm: 0.5rem;
  --radius-md: 0.75rem;
  --radius-lg: 1rem;
  --radius-xl: 1.5rem;
  --radius-full: 9999px;
}
```

### Recommended Component Documentation

| Component | Class | Props | Usage |
|-----------|-------|-------|-------|
| Button | `neu-btn` | `primary`, `icon`, `sm`, `lg` | Actions, navigation |
| Card | `neu-card` | — | Content containers |
| Input | `neu-input` | `error`, `disabled` | Forms |
| Badge | `neu-badge` | `accent`, `success`, `danger` | Status indicators |
| Toggle | `neu-toggle` | `checked` | Settings switches |

---

## F. 💬 پیام کامیت پیشنهادی

```
docs: update UI/UX audit report — add critical issues, medium issues, enhancement suggestions, design system recommendation
```

---

**Overall Score:** 8.5/10 — Professional, modern, well-implemented frontend with excellent RTL support and neumorphism design.

### Top 5 Quick Wins
1. Remove duplicate Vazirmatn font import (saves ~200KB)
2. Add skip-to-content link (accessibility)
3. Add `alt` to product thumbnails (accessibility)
4. Create custom 404 page (UX)
5. Add `<link rel="preload">` for critical font (performance)
