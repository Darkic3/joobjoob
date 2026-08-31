# 🛒 داکیومنت جامع مرجع سیستم سفارشات فروشگاه Laravel

> این داکیومنت شامل تمام کدهای مرتبط با سیستم سفارشات، پرداخت، ارسال، و مدیریت فروشگاه است.
> ساختار پروژه: ماژولار Laravel با namespace اصلی `Rahweb\CmsCore\Modules\`

---

## فهرست مطالب

1. [دیتابیس و مدل‌ها](#۱-دیتابیس-و-مدل‌ها)
2. [فرآیند تسویه حساب (Checkout)](#۲-فرآیند-تسویه-حساب-checkout)
3. [پرداخت](#۳-پرداخت)
4. [ارسال و حمل‌ونقل](#۴-ارسال-و-حملونقل)
5. [پنل کاربری](#۵-پنل-کاربری)
6. [پنل ادمین](#۶-پنل-ادمین)
7. [روت‌ها](#۷-روت‌ها)
8. [سرویس‌ها و کلاس‌های کمکی](#۸-سرویسها-و-کلاسهای-کمکی)

---

# ۱. دیتابیس و مدل‌ها

## ۱.۱ مایگریشن جدول orders

### مایگریشن اصلی

```php
<?php
// Database/Migrations/2024_09_09_061418_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->Integer('user_id')->nullable();
            $table->Integer('address_id')->nullable();
            $table->Integer('state_id')->nullable();
            $table->Integer('city_id')->nullable();
            $table->text('address')->nullable();
            $table->string('receiptor_full_name')->nullable();
            $table->Integer('shipping_status_id')->nullable();
            $table->string('order_status')->nullable();
            $table->Integer('basket_id')->nullable();
            $table->Integer('bank_id')->nullable();
            $table->Integer('shipping_method_id')->nullable();
            $table->Integer('discount_id')->nullable();
            $table->string('shipping_price')->nullable();
            $table->string('total_price')->nullable();
            $table->string('discount_price')->nullable();
            $table->string('payment_price')->nullable();
            $table->json('transaction_info')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
```

### Alter: اضافه کردن فیلدهای مالیات

```php
<?php
// Database/Migrations/2025_01_13_170644_add_tax_fields_to_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tax_price')->after('payment_price')->nullable();
            $table->string('current_tax')->after('payment_price')->nullable();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: اضافه کردن وزن کل

```php
<?php
// Database/Migrations/2025_03_02_141454_add_total_weight_to_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('total_weight')->nullable()->after('discount_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: کد پستی و پس کرایه

```php
<?php
// Database/Migrations/2025_03_03_095601_add_post_code_to_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('post_code')->nullable()->after('discount_id');
            $table->integer('freight_balance')->default(0)->after('order_status');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: توضیحات کاربر

```php
<?php
// Database/Migrations/2025_04_07_152954_add_user_description_to_orderstable.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('user_description')->nullable()->after('tax_price');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: ایندکس روی user_id

```php
<?php
// Database/Migrations/2025_04_14_120547_add_index_to_fields_to_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: بیعانه و مبلغ باقیمانده

```php
<?php
// Database/Migrations/2025_05_18_152720_add_deposit_price_to_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('deposit_price')->nullable()->after('payment_price');
            $table->string('remaining_price')->nullable()->after('payment_price');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
```

### خلاصه نهایی فیلدهای جدول orders

| فیلد | تایپ | توضیح |
|------|------|-------|
| id | bigint (AI) | کلید اصلی |
| user_id | int, nullable | کلید خارجی کاربر |
| address_id | int, nullable | کلید خارجی آدرس |
| state_id | int, nullable | کلید خارجی استان |
| city_id | int, nullable | کلید خارجی شهر |
| address | text, nullable | آدرس JSON (شامل state, city, address, receiptor_mobile, postal_code) |
| receiptor_full_name | string, nullable | نام تحویل‌گیرنده |
| shipping_status_id | int, nullable | وضعیت ارسال |
| order_status | string, nullable | وضعیت سفارش (paying, paid, deposit_paid, unpaid, cancelled, wait_for_verification) |
| basket_id | int, nullable | سبد خرید مرجع |
| bank_id | int, nullable | درگاه پرداخت |
| shipping_method_id | int, nullable | روش ارسال |
| discount_id | int, nullable | کد تخفیف |
| shipping_price | string, nullable | هزینه ارسال (تومان) |
| total_price | string, nullable | قیمت کل |
| discount_price | string, nullable | مبلغ تخفیف |
| payment_price | string, nullable | مبلغ قابل پرداخت |
| tax_price | string, nullable | مبلغ مالیات |
| current_tax | string, nullable | درصد مالیات |
| deposit_price | string, nullable | مبلغ بیعانه |
| remaining_price | string, nullable | مبلغ باقیمانده |
| total_weight | double, nullable | وزن کل (گرم) |
| post_code | string, nullable | کد پیگیری پستی |
| freight_balance | int, default 0 | پس کرایه (1=بله) |
| user_description | text, nullable | توضیحات کاربر |
| transaction_info | json, nullable | اطلاعات تراکنش (post + verify) |
| deleted_at | timestamp | Soft Delete |
| created_at | timestamp | تاریخ ایجاد |
| updated_at | timestamp | تاریخ بروزرسانی |

---

## ۱.۲ مایگریشن جدول order_items

### مایگریشن اصلی

```php
<?php
// Database/Migrations/2024_09_09_061514_create_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->Integer('order_id')->nullable();
            $table->Integer('product_id')->nullable();
            $table->Integer('product_variant_id')->nullable();
            $table->string('quantity')->nullable();
            $table->string('price')->nullable();
            $table->string('discounted_price')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
```

### Alter: وزن آیتم

```php
<?php
// Database/Migrations/2025_03_02_141304_add_weight_to_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->double('weight')->nullable()->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: ایندکس‌ها

```php
<?php
// Database/Migrations/2025_04_14_121016_add_index_to_fields_to_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: تعداد قبلی (مرجوعی)

```php
<?php
// Database/Migrations/2025_09_07_102552_add_old_quantity_to_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('old_quantity')->after('quantity')->nullable();
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
```

### خلاصه نهایی فیلدهای جدول order_items

| فیلد | تایپ | توضیح |
|------|------|-------|
| id | bigint (AI) | کلید اصلی |
| order_id | int, nullable | سفارش والد |
| product_id | int, nullable | محصول |
| product_variant_id | int, nullable | واریانت محصول |
| quantity | string, nullable | تعداد |
| old_quantity | string, nullable | تعداد قبل از مرجوعی |
| weight | double, nullable | وزن |
| price | string, nullable | قیمت واحد |
| discounted_price | string, nullable | قیمت با تخفیف |

---

## ۱.۳ مایگریشن جدول baskets (سبد خرید)

```php
<?php
// Database/Migrations/2024_09_09_061657_create_baskets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('baskets', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->Integer('user_id')->nullable();
            $table->Integer('address_id')->nullable();
            $table->Integer('bank_id')->nullable();
            $table->Integer('shipping_method_id')->nullable();
            $table->Integer('discount_id')->nullable();
            $table->text('user_cookie')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('baskets');
    }
};
```

---

## ۱.۴ مایگریشن جدول basket_items

```php
<?php
// Database/Migrations/2024_09_09_061637_create_basket_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('basket_items', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->Integer('basket_id')->nullable();
            $table->Integer('product_id')->nullable();
            $table->Integer('product_variant_id')->nullable();
            $table->string('quantity')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('basket_items');
    }
};
```

### Alter: ایندکس‌ها

```php
<?php
// Database/Migrations/2025_04_14_120926_add_index_to_fields_to_basket_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('basket_items', function (Blueprint $table) {
            $table->index('basket_id');
            $table->index('product_id');
        });
    }

    public function down()
    {
        Schema::table('basket_items', function (Blueprint $table) {
            //
        });
    }
};
```

---

## ۱.۵ مایگریشن جدول order_shipping_statuses (وضعیت ارسال)

### مایگریشن اصلی

```php
<?php
// Database/Migrations/2024_09_07_102753_create_order_shipping_statuses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_shipping_statuses', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->string('title')->nullable();
            $table->string('color')->nullable();
            $table->tinyInteger('default')->default(0);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_shipping_statuses');
    }
};
```

### Alter: فیلد آماده ارسال

```php
<?php
// Database/Migrations/2025_03_01_131520_add_for_send_to_order_shipping_statuses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_shipping_statuses', function (Blueprint $table) {
            $table->tinyInteger('for_send')->default(0)->after('default');
        });
    }

    public function down()
    {
        Schema::table('order_shipping_statuses', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: ارسال پیامک

```php
<?php
// Database/Migrations/2025_03_03_222341_add_sending_sms_to_order_shipping_statuses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_shipping_statuses', function (Blueprint $table) {
            $table->tinyInteger('sending_sms')->default(1)->after('default');
        });
    }

    public function down()
    {
        Schema::table('order_shipping_statuses', function (Blueprint $table) {
            //
        });
    }
};
```

---

## ۱.۶ مایگریشن جدول shipping_methods (روش‌های ارسال)

### مایگریشن اصلی

```php
<?php
// Database/Migrations/2024_09_08_092702_create_shipping_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->string('title')->nullable();
            $table->string('price')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_methods');
    }
};
```

### Alter: سقف قیمت + توضیحات + پس کرایه

```php
<?php
// Database/Migrations/2025_02_21_204205_add_price_ceiling_to_shipping_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->text('description')->nullable()->after('status');
            $table->integer('freight_balance')->default(0)->after('status');
            $table->double('price_ceiling')->default(0)->after('price');
        });
    }

    public function down()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: نوع ارسال (fixed_price/with_weight/chapar)

```php
<?php
// Database/Migrations/2025_02_21_213453_add_type_to_shipping_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->string('type')->default('fixed_price')->after('price_ceiling');
            $table->string('chapar_type')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: کانفیگ چاپار

```php
<?php
// Database/Migrations/2025_02_22_205303_add_migrate_config_to_shipping_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->json('config')->after('description')->nullable();
        });
    }

    public function down()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            //
        });
    }
};
```

### Alter: اطلاعات فرستنده

```php
<?php
// Database/Migrations/2025_03_01_214341_add_sender_information_to_shipping_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->string('sender_name')->nullable()->after('description');
            $table->string('sender_company')->nullable()->after('description');
            $table->integer('sender_city_id')->nullable()->after('description');
            $table->string('sender_phone')->nullable()->after('description');
            $table->string('sender_mobile')->nullable()->after('description');
            $table->string('sender_email')->nullable()->after('description');
            $table->text('sender_address')->nullable()->after('description');
            $table->string('sender_postal_code')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            //
        });
    }
};
```

---

## ۱.۷ مایگریشن جدول shipping_method_city (pivot)

```php
<?php
// Database/Migrations/2024_09_08_093042_create_shipping_method_city_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipping_method_city', function (Blueprint $table) {
            $table->integer('shipping_method_id')->nullable();
            $table->integer('city_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_method_city');
    }
};
```

---

## ۱.۸ مایگریشن جدول discounts (تخفیف‌ها)

### مایگریشن اصلی

```php
<?php
// Database/Migrations/2024_09_08_121453_create_discounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->string('title')->nullable();
            $table->string('amount')->nullable();
            $table->string('type')->nullable();
            $table->Integer('count')->default(1);
            $table->string('basket_minimum_price')->nullable();
            $table->tinyInteger('first_purchase')->default(0);
            $table->tinyInteger('with_discount')->default(0);
            $table->tinyInteger('user_id')->nullable();
            $table->Integer('max_usage_per_user')->default(1);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('discounts');
    }
};
```

### Alter: تغییر نوع user_id از tinyint به int

```php
<?php
// Database/Migrations/2025_08_30_102106_change_user_id_type_discounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE discounts MODIFY COLUMN user_id INT');
    }

    public function down()
    {
        DB::statement('ALTER TABLE discounts MODIFY COLUMN user_id TINYINT');
    }
};
```

### Alter: نوع پرداخت (category/brand/product)

```php
<?php
// Database/Migrations/2025_12_28_134517_add_pay_type_to_discounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->string('pay_type')->nullable()->after('max_usage_per_user');
        });
    }

    public function down()
    {
        Schema::table('discounts', function (Blueprint $table) {
            //
        });
    }
};
```

---

## ۱.۹ مایگریشن جدول discountable (polymorphic pivot)

```php
<?php
// Database/Migrations/2025_10_18_140337_create_discountable_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('discountable', function (Blueprint $table) {
            $table->integer('discount_id');
            $table->morphs('discountable');
        });
    }

    public function down()
    {
        Schema::dropIfExists('discountable');
    }
};
```

---

## ۱.۱۰ مایگریشن جدول banks (درگاه‌های بانکی)

### مایگریشن اصلی

```php
<?php
// Database/Migrations/2024_09_03_124918_create_banks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->string('title')->nullable();
            $table->string('icon')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('bank_type')->nullable();
            $table->json('config')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banks');
    }
};
```

### Alter: فیلد تست ادمین

```php
<?php
// Database/Migrations/2025_10_28_081715_add_field_admin_test_to_banks.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->boolean('admin_test')->default(0)->after('status');
        });
    }

    public function down()
    {
        Schema::table('banks', function (Blueprint $table) {
            //
        });
    }
};
```

---

## ۱.۱۱ مایگریشن جدول order_histories (تاریخچه سفارش)

```php
<?php
// Database/Migrations/2024_09_09_061540_create_order_histories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->Integer('order_id')->nullable();
            $table->Integer('shipping_status_id')->nullable();
            $table->string('order_status')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_histories');
    }
};
```

---

## ۱.۱۲ مایگریشن جدول inventory_transactions (تراکنش‌های موجودی)

```php
<?php
// Database/Migrations/2024_09_24_071052_create_inventory_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->Integer('product_id')->nullable();
            $table->Integer('product_variant_id')->nullable();
            $table->Integer('quantity')->nullable();
            $table->string('type')->nullable();
            $table->Integer('order_id')->nullable();
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
```

---

## ۱.۱۳ مایگریشن جدول order_images

```php
<?php
// Database/Migrations/2025_05_18_144924_create_order_images_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_images', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->Integer('order_id')->nullable();
            $table->string('file')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_images');
    }
};
```

---

## ۱.۱۴ مایگریشن‌های Location (استان، شهر، آدرس)

### states

```php
<?php
// Location/Database/Migrations/2024_09_08_053328_create_states_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->string('name')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('states');
    }
};
```

### Alter: chapar_id برای states

```php
<?php
// Location/Database/Migrations/2025_02_21_222116_add_chapar_id_to_states_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('states', function (Blueprint $table) {
            $table->integer('chapar_id')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('states', function (Blueprint $table) {
            //
        });
    }
};
```

### cities

```php
<?php
// Location/Database/Migrations/2024_09_08_053413_create_cities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->string('name')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->Integer('state_id')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cities');
    }
};
```

### Alter: chapar_id برای cities

```php
<?php
// Location/Database/Migrations/2025_02_21_222137_add_chapar_id_to_cities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->integer('chapar_id')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            //
        });
    }
};
```

### addresses

```php
<?php
// Location/Database/Migrations/2024_09_09_103143_create_addresses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id()->BigIncrement();
            $table->Integer('user_id')->nullable();
            $table->Integer('state_id')->nullable();
            $table->Integer('city_id')->nullable();
            $table->text('address')->nullable();
            $table->string('receiptor_full_name')->nullable();
            $table->string('receiptor_mobile')->nullable();
            $table->string('postal_code')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('addresses');
    }
};
```

---

## ۱.۱۵ مدل Order

```php
<?php
// Modules/Order/Entities/Order.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use App\Library\SiteHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Location\Entities\Address;
use Rahweb\CmsCore\Modules\Location\Entities\City;
use Rahweb\CmsCore\Modules\Location\Entities\State;
use Rahweb\CmsCore\Modules\User\Entities\User;

class Order extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'address_id', 'state_id', 'city_id', 'address', 'receiptor_full_name',
        'shipping_status_id', 'order_status', 'basket_id', 'bank_id', 'shipping_method_id',
        'discount_id', 'shipping_price', 'total_price', 'discount_price', 'payment_price',
        'transaction_info', 'current_tax', 'tax_price', 'created_at', 'updated_at',
        'total_weight', 'post_code', 'freight_balance', 'user_description',
        'deposit_price', 'remaining_price'
    ];

    // ==================== Scopes ====================

    public function scopeAuthUser($query)
    {
        return $query->where('user_id', Auth::id());
    }

    // ==================== Relationships ====================

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->withTrashed();
    }

    public function location()
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }

    public function state()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }

    public function shipping_status()
    {
        return $this->hasOne(OrderShippingStatus::class, 'id', 'shipping_status_id');
    }

    public function basket()
    {
        return $this->hasOne(Basket::class, 'id', 'basket_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }

    public function shipping_method()
    {
        return $this->hasOne(ShippingMethod::class, 'id', 'shipping_method_id');
    }

    public function discount()
    {
        return $this->hasOne(Discount::class, 'id', 'discount_id')->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class)->where('quantity', '>', 0)->with('product');
    }

    public function allItems()
    {
        return $this->hasMany(OrderItem::class)->with('product');
    }

    public function images()
    {
        return $this->hasMany(OrderImage::class, 'order_id', 'id');
    }

    // ==================== Accessors ====================

    public function getDateAttribute()
    {
        return jdate('d F Y', $this->created_at->timestamp);
    }

    public function getTimeAttribute()
    {
        return jdate('H:i', $this->created_at->timestamp);
    }

    public function getStatusAttribute()
    {
        switch ($this->order_status) {
            case 'paying':
                return ['title' => 'در حال پرداخت', 'badge' => 'warning'];
            case 'paid':
                return ['title' => 'پرداخت شده', 'badge' => 'success'];
            case 'deposit_paid':
                return ['title' => 'بیعانه پرداخت شده', 'badge' => 'success'];
            case 'wait_for_verification':
                return ['title' => 'در انتظار تایید فیش ها', 'badge' => 'success'];
            case 'unpaid':
                return ['title' => 'پرداخت نشده', 'badge' => 'danger'];
            case 'cancelled':
                return ['title' => 'لغو شده', 'badge' => 'danger'];
            default:
                return null;
        }
    }

    public function getShippingNameAttribute()
    {
        switch (true) {
            case intval($this->shipping_price) == 0 && $this->freight_balance == 0:
                return 'ارسال رایگان';
            case intval($this->shipping_price) != 0 && $this->freight_balance == 0:
                return number_format($this->shipping_price) . ' تومان';
            default:
                return '';
        }
    }

    public function getFreightBalanceNameAttribute()
    {
        return $this->attributes['freight_balance'] == 1
            ? ['title' => 'پس کرایه  ', 'badge' => 'success']
            : ['title' => 'پیش کرایه  ', 'badge' => 'info'];
    }

    public function getBankTrackingCodeAttribute()
    {
        $transaction = json_decode($this->transaction_info, true);
        $bank_type = @$this->bank->bank_type;
        switch ($bank_type) {
            case "sep":
                return $transaction['verify']['TraceNo'] ?? '';
            case "zarinPal":
                return $transaction['verify']['RefID'] ?? '';
            case "sadad":
                return @$transaction['verify']['RetrivalRefNo'] ?? '';
            default:
                return '';
        }
    }

    // ==================== Methods ====================

    public function hasReturnItem()
    {
        $hasOld = $this->allItems()->whereNotNull('old_quantity')->exists();
        if ($hasOld) {
            return '<span class="badge bg-label-danger">دارای  مرجوعی</span>';
        }
        return null;
    }
}
```

---

## ۱.۱۶ مدل OrderItem

```php
<?php
// Modules/Order/Entities/OrderItem.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rahweb\CmsCore\Modules\Product\Entities\Product;
use Rahweb\CmsCore\Modules\Product\Entities\ProductVariant;

class OrderItem extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'quantity',
        'price', 'discounted_price', 'weight', 'old_quantity'
    ];

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')->withTrashed();
    }

    public function product_variant()
    {
        return $this->hasOne(ProductVariant::class, 'id', 'product_variant_id')->withTrashed();
    }

    public function getDateAttribute()
    {
        $date = jdate('d F Y', $this->updated_at->timestamp);
        return $date;
    }
}
```

---

## ۱.۱۷ مدل Basket

```php
<?php
// Modules/Order/Entities/Basket.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use App\Library\SiteHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Location\Entities\Address;
use Rahweb\CmsCore\Modules\User\Entities\User;

class Basket extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'address_id', 'bank_id', 'shipping_method_id',
        'discount_id', 'user_cookie',
    ];

    // ==================== Scopes ====================

    public function scopeAuthUser($query)
    {
        $site_name = @SiteHelper::getInformation()['site_name'];
        if (Auth::check()) {
            return $query->where('user_id', Auth::id());
        } else {
            if (session()->get('custom_data') == null) {
                session()->put('custom_data', $site_name . strtotime(Carbon::now()));
                session()->save();
            }
            return $query->whereNotNull('user_cookie')
                ->where('user_cookie', session()->get('custom_data'));
        }
    }

    public function scopeCookieUser($query)
    {
        $site_name = @SiteHelper::getInformation()['site_name'];
        if (session()->get('custom_data') == null) {
            session()->put('custom_data', $site_name . strtotime(Carbon::now()));
            session()->save();
        }
        return $query->whereNotNull('user_cookie')
            ->where('user_cookie', session()->get('custom_data'));
    }

    // ==================== Relationships ====================

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function address()
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }

    public function shippingMethod()
    {
        return $this->hasOne(ShippingMethod::class, 'id', 'shipping_method_id');
    }

    public function discount()
    {
        return $this->hasOne(Discount::class, 'id', 'discount_id');
    }

    public function items()
    {
        return $this->hasMany(BasketItem::class)
            ->where('quantity', '>', 0)
            ->orderBy('id', 'DESC')
            ->with('product');
    }

    // ==================== Accessors ====================

    public function getFinalPriceAttribute()
    {
        $final_price_sum = 0;
        foreach ($this->items as $item) {
            $variant = $item->productVariant;
            $product = $item->product;
            if ($variant) {
                $final = $variant->final_price ?? 0;
            } elseif ($product) {
                $final = $product->final_price ?? 0;
            } else {
                $final = 0;
            }
            $final_price_sum += $final * ($item->quantity ?? 0);
        }
        return $final_price_sum;
    }

    public function getDateAttribute()
    {
        $date = jdate('d F Y', $this->updated_at->timestamp);
        return $date;
    }
}
```

---

## ۱.۱۸ مدل BasketItem

```php
<?php
// Modules/Order/Entities/BasketItem.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rahweb\CmsCore\Modules\Product\Entities\Product;
use Rahweb\CmsCore\Modules\Product\Entities\ProductVariant;

class BasketItem extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'basket_id', 'product_id', 'product_variant_id', 'quantity',
    ];

    public function basket()
    {
        return $this->hasOne(Basket::class, 'id', 'basket_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function productVariant()
    {
        return $this->hasOne(ProductVariant::class, 'id', 'product_variant_id');
    }

    public function getDateAttribute()
    {
        $date = jdate('d F Y', $this->updated_at->timestamp);
        return $date;
    }
}
```

---

## ۱.۱۹ مدل Discount

```php
<?php
// Modules/Order/Entities/Discount.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rahweb\CmsCore\Modules\Product\Entities\Brand;
use Rahweb\CmsCore\Modules\Product\Entities\ProductCategory;
use Rahweb\CmsCore\Modules\User\Entities\User;

class Discount extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'title', 'amount', 'type', 'count', 'basket_minimum_price',
        'first_purchase', 'with_discount', 'user_id', 'max_usage_per_user', 'pay_type'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'discount_id', 'id');
    }

    public function discountable()
    {
        return $this->morphMany(Discountable::class, 'discountable');
    }

    public function productCategories()
    {
        return $this->morphedByMany(
            ProductCategory::class, 'discountable', 'discountable', 'discount_id', 'discountable_id'
        );
    }

    public function brands()
    {
        return $this->morphedByMany(
            Brand::class, 'discountable', 'discountable', 'discount_id', 'discountable_id'
        );
    }

    public function getDateAttribute()
    {
        $date = jdate('d F Y', $this->updated_at->timestamp);
        return $date;
    }

    public function getAmountNameAttribute()
    {
        return $this->attributes['type'] == "cash"
            ? $this->attributes['amount'] . ' تومان '
            : $this->attributes['amount'] . ' درصد ';
    }
}
```

---

## ۱.۲۰ مدل ShippingMethod

```php
<?php
// Modules/Order/Entities/ShippingMethod.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use Rahweb\CmsCore\Modules\General\Helper\NumberHelper;
use Rahweb\CmsCore\Modules\Location\Entities\City;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'price', 'status', 'description', 'freight_balance',
        'price_ceiling', 'type', 'chapar_type', 'config',
        'sender_name', 'sender_company', 'sender_city_id', 'sender_phone',
        'sender_mobile', 'sender_address', 'sender_postal_code', 'sender_email',
    ];

    public function cities()
    {
        return $this->belongsToMany(City::class, 'shipping_method_city');
    }

    public function sender_city()
    {
        return $this->hasOne(City::class, 'id', 'sender_city_id');
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = NumberHelper::persian2LatinDigit($value);
    }

    public function setPriceCeilingAttribute($value)
    {
        $this->attributes['price_ceiling'] = NumberHelper::persian2LatinDigit($value);
    }

    public function getTypeNameAttribute()
    {
        switch ($this->attributes['type']) {
            case "chapar":
                return ['title' => 'چاپار', 'badge' => 'success'];
            case "with_weight":
                return ['title' => 'بر اساس وزن', 'badge' => 'info'];
            default:
                return ['title' => 'با قیمت ثابت', 'badge' => 'info'];
        }
    }

    public function getFreightBalanceNameAttribute()
    {
        return $this->attributes['freight_balance'] == 1
            ? ['title' => 'پس کرایه  ', 'badge' => 'success']
            : ['title' => 'پیش کرایه  ', 'badge' => 'info'];
    }
}
```

---

## ۱.۲۱ مدل OrderShippingStatus

```php
<?php
// Modules/Order/Entities/OrderShippingStatus.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OrderShippingStatus extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'title', 'color', 'default', 'for_send', 'sending_sms'
    ];

    public function getDateAttribute()
    {
        $date = jdate('d F Y', $this->updated_at->timestamp);
        return $date;
    }

    public function getSendNameAttribute()
    {
        return $this->attributes['for_send'] == 1
            ? ['title' => 'آماده ارسال ', 'badge' => 'success']
            : ['title' => 'عادی ', 'badge' => 'warning'];
    }
}
```

---

## ۱.۲۲ مدل Bank

```php
<?php
// Modules/Order/Entities/Bank.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'icon', 'status', 'admin_test', 'bank_type', 'config',
    ];

    protected $appends = ['item_image'];

    public function getItemImageAttribute()
    {
        return (asset('assets/site/bank/' . $this->attributes['icon']));
    }

    public function scopeActive($query)
    {
        return $query->whereStatus('1');
    }
}
```

---

## ۱.۲۳ مدل OrderHistory

```php
<?php
// Modules/Order/Entities/OrderHistory.php

namespace Rahweb\CmsCore\Modules\Order\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OrderHistory extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'shipping_status_id', 'order_status',
    ];

    public function shipping_status()
    {
        return $this->hasOne(OrderShippingStatus::class, 'id', 'shipping_status_id');
    }

    public function getStatusNameAttribute()
    {
        switch ($this->order_status) {
            case 'paying': return 'در حال پرداخت';
            case 'paid': return 'پرداخت شده';
            case 'unpaid': return 'پرداخت نشده';
            case 'cancelled': return 'لغو شده';
            default: return '';
        }
    }
}
```

---

## ۱.۲۴ مدل‌های Location

### State

```php
<?php
// Modules/Location/Entities/State.php

namespace Rahweb\CmsCore\Modules\Location\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class State extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name', 'status', 'chapar_id'
    ];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
```

### City

```php
<?php
// Modules/Location/Entities/City.php

namespace Rahweb\CmsCore\Modules\Location\Entities;

use Rahweb\CmsCore\Modules\Order\Entities\ShippingMethod;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class City extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name', 'status', 'state_id', 'chapar_id', 'id'
    ];

    public function state()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function shippingMethods()
    {
        return $this->belongsToMany(
            ShippingMethod::class, "shipping_method_city", "city_id", "shipping_method_id"
        );
    }

    public function availableShippingMethods()
    {
        return $this->belongsToMany(
            ShippingMethod::class, "shipping_method_city", "city_id", "shipping_method_id"
        )->whereStatus(1);
    }
}
```

### Address

```php
<?php
// Modules/Location/Entities/Address.php

namespace Rahweb\CmsCore\Modules\Location\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class Address extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'state_id', 'city_id', 'address',
        'receiptor_full_name', 'receiptor_mobile', 'postal_code'
    ];

    public function scopeAuthUser($query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function state()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }
}
```

---

## ۱.۲۵ مدل User (فقط روابط مرتبط)

```php
<?php
// Modules/User/Entities/User.php

namespace Rahweb\CmsCore\Modules\User\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rahweb\CmsCore\Modules\Location\Entities\Address;
use Rahweb\CmsCore\Modules\Order\Entities\Basket;
use Rahweb\CmsCore\Modules\Order\Entities\Order;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'full_name', 'mobile', 'email', 'avatar', 'password',
        'show_in_first_page', 'confirm_code', 'birthday'
    ];

    protected $hidden = ['password'];

    // ==================== Relationships ====================

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id')->where('order_status', 'paid');
    }

    public function basket()
    {
        return $this->hasOne(Basket::class, 'user_id', 'id')->orderBy('id', 'DESC');
    }

    public function ordersWithDeposit()
    {
        return $this->hasMany(Order::class, 'user_id', 'id')
            ->whereIn('order_status', ['paid', 'deposit_paid', 'wait_for_verification'])
            ->orderBy('id', 'DESC');
    }

    public function ordersWithDiscount($discountId)
    {
        return $this->hasMany(Order::class, 'user_id', 'id')
            ->where('order_status', 'paid')
            ->where('discount_id', $discountId);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
```

---

# ۲. فرآیند تسویه حساب (Checkout)

## ۲.۱ Site BasketController (افزودن به سبد)

```php
<?php
// app/Http/Controllers/Shop/BasketController.php

namespace App\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rahweb\CmsCore\Modules\Order\Http\Resources\BasketCollection;
use Rahweb\CmsCore\Modules\Order\Services\BasketService;
use Rahweb\CmsCore\Modules\Product\Entities\Product;

class BasketController extends Controller
{
    public function itemCount()
    {
        $basket = BasketService::findBasketAndCheckStock();
        $basketCount = $basket ? @$basket->items()->sum('quantity') : 0;
        return response()->json([
            'basketCount' => @$basketCount,
        ], 200);
    }

    public function add(Request $request)
    {
        $product_id = @$request->get('product_id');
        $product_variant_id = @$request->get('product_variant_id');
        $quantity = @$request->get('quantity');
        $cart = @$request->get('cart');

        return BasketService::create($product_id, $product_variant_id, $quantity, $cart);
    }

    public function cart()
    {
        $basket = BasketService::findBasketAndCheckStock();
        if ($basket && $basket->discounted_id != null) {
            $basket->update(['discounted_id' => null]);
        }
        return view('pages.cart.checkout', compact('basket'));
    }

    public function cartItems()
    {
        $basket = BasketService::findBasketAndCheckStock();
        $basketCollection = $basket ? new BasketCollection(@$basket->items) : [];
        return response()->json([
            'basketCollection' => @$basketCollection,
        ], 200);
    }

    public function delete()
    {
        BasketService::delete();
        return redirect()->back()->with('success', 'سبد خرید با موفقیت حذف شد');
    }

    public function removeItem(Request $request)
    {
        BasketService::removeItem($request->get('item_id'));
        return redirect()->back()->with('success', 'محصول موردنظر با موفقیت حذف شد');
    }

    public function listPrice()
    {
        $price = BasketService::listPrice();
        return response()->json($price);
    }
}
```

---

## ۲.۲ Site AddressController (انتخاب آدرس و روش ارسال)

```php
<?php
// app/Http/Controllers/Shop/AddressController.php

namespace App\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\General\Helper\NumberHelper;
use Rahweb\CmsCore\Modules\Location\Entities\Address;
use Rahweb\CmsCore\Modules\Location\Http\Resources\AddressCollection;
use Rahweb\CmsCore\Modules\Location\Services\AddressService;
use Rahweb\CmsCore\Modules\Location\Services\CityService;
use Rahweb\CmsCore\Modules\Location\Services\StateService;
use Rahweb\CmsCore\Modules\Order\Services\BasketService;

class AddressController extends Controller
{
    // نمایش صفحه انتخاب آدرس
    public function list()
    {
        $basket = BasketService::findBasketAndCheckStock();
        if (!$basket) {
            return redirect(route('basket.cart'));
        }
        return view('pages.cart.address', compact('basket'));
    }

    // دریافت آدرس‌های کاربر (AJAX)
    public function userAddresses()
    {
        $addresses = AddressService::find();
        $addressCollection = new AddressCollection($addresses['addresses']);
        return response()->json([
            'addresses' => @$addressCollection,
        ], 200);
    }

    // دریافت لیست استان‌ها (AJAX)
    public function states()
    {
        $states = StateService::findAll();
        return response()->json(['states' => $states]);
    }

    // دریافت شهرها بر اساس استان (AJAX)
    public function city(Request $request)
    {
        $req = $request->all();
        $cities = CityService::findOne($req['state_id']);
        return response()->json(['cities' => $cities]);
    }

    // ایجاد آدرس جدید
    public function create(Request $request)
    {
        $user_id = @Auth::id();
        $state_id = @$request->get('state_id');
        $city_id = @$request->get('city_id');
        $address = @$request->get('address');
        $postal_code = NumberHelper::persian2LatinDigit(@$request->get('postal_code'));
        $receiptor_full_name = @$request->get('receiptor_full_name');
        $receiptor_mobile = NumberHelper::persian2LatinDigit(@$request->get('receiptor_mobile'));
        AddressService::create($user_id, $state_id, $city_id, $address, $postal_code, $receiptor_full_name, $receiptor_mobile);
        return redirect()->back()->with('success', 'آدرس با موفقیت اضافه شد');
    }

    // دریافت آدرس برای ویرایش (AJAX)
    public function edit(Request $request)
    {
        $address = AddressService::findOne($request->get('address_id'));
        return response()->json(['address' => $address]);
    }

    // بروزرسانی آدرس
    public function update(Request $request)
    {
        $user_id = @Auth::id();
        $address_id = @$request->get('address_id');
        $state_id = @$request->get('state_id');
        $city_id = @$request->get('city_id');
        $address = @$request->get('address');
        $postal_code = NumberHelper::persian2LatinDigit(@$request->get('postal_code'));
        $receiptor_full_name = @$request->get('receiptor_full_name');
        $receiptor_mobile = NumberHelper::persian2LatinDigit(@$request->get('receiptor_mobile'));
        AddressService::update($address_id, $user_id, $state_id, $city_id, $address, $postal_code, $receiptor_full_name, $receiptor_mobile);
        return redirect()->back()->with('success', 'آدرس با موفقیت ویرایش شد');
    }

    // دریافت روش‌های ارسال بر اساس آدرس انتخاب شده (AJAX)
    public function shipments(Request $request)
    {
        $address = AddressService::findOne($request->get('address_id'));
        $basket = BasketService::findBasketAndCheckStock();
        $basket->update(['address_id' => @$request->get('address_id')]);
        $shipping_methods = BasketService::checkShipmentPriceBaseOnAddress($address);
        return response()->json(['shipping_methods' => @$shipping_methods]);
    }

    // انتخاب روش ارسال (AJAX)
    public function setShipments(Request $request)
    {
        try {
            $basket = BasketService::findBasketAndCheckStock();
            $basket->update([
                'shipping_method_id' => @$request->get('shipping_method_id')
            ]);
        } catch (\Exception $err) {
            return Redirect::back()->with('error', $err->getMessage());
        }
    }

    // محاسبه قیمت نهایی (AJAX)
    public function addressPrice()
    {
        try {
            $price = BasketService::addressPrice();
            return response()->json($price);
        } catch (\Exception $err) {
            $basket = BasketService::findBasketAndCheckStock();
            $basket->update(['shipping_method_id' => null]);
            return response()->json(['error' => $err->getMessage()], 500);
        }
    }
}
```

---

## ۲.۳ Site OrderController (پرداخت و ایجاد سفارش)

```php
<?php
// app/Http/Controllers/Shop/OrderController.php

namespace App\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Order\Http\Resources\BasketCollection;
use Rahweb\CmsCore\Modules\Order\Services\BankService;
use Rahweb\CmsCore\Modules\Order\Services\BasketService;
use Rahweb\CmsCore\Modules\Order\Services\DiscountService;
use Rahweb\CmsCore\Modules\Order\Services\OrderService;
use Rahweb\CmsCore\Modules\Order\Services\SnappPayService;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    // صفحه پرداخت (مرحله ۳)
    public function cart()
    {
        $basket = BasketService::findBasketAndCheckStock();
        if (!isset($basket)) {
            return redirect()->route('basket.cart');
        }
        if ($basket->address_id == null) {
            return redirect()->route('basket.shipping')
                ->with('error', 'لطفا ابتدا آدرس را انتخاب کنید');
        }
        if (count(@$basket->address->city->shippingMethods) == 0) {
            return redirect()->route('basket.shipping')
                ->with('error', 'روش ارسال اجباریست لطفا با پشتیبانی تماس بگیرید');
        }
        if (count(@$basket->address->city->shippingMethods) > 0 && $basket->shipping_method_id == null) {
            return redirect()->route('basket.shipping')
                ->with('error', 'لطفا ابتدا روش ارسال را انتخاب کنید');
        }

        $items = $basket ? new BasketCollection(@$basket->items) : [];
        $banks = BankService::findAllActive();
        return view('pages.cart.payment', compact('basket', 'items', 'banks'));
    }

    // اعمال کد تخفیف (AJAX)
    public function addDiscount(Request $request)
    {
        return DiscountService::checkDiscount($request->get('discount_code'));
    }

    // حذف کد تخفیف (AJAX)
    public function deleteDiscount(Request $request)
    {
        $basket = BasketService::findBasketAndCheckStock();
        $basket->update(['discount_id' => null]);
    }

    // دریافت قیمت نهایی (AJAX)
    public function orderPrice()
    {
        try {
            $price = DiscountService::listPrice();
            $banks = BankService::findAllActive();
            $basket = BasketService::findBasketAndCheckStock();
            $price['snapp_data'] = [];
            if (in_array('snappay', $banks->pluck('bank_type')->toArray())) {
                if ($basket->discount_id == null) {
                    $snapp_price = $basket->final_price;
                } else {
                    $snapp_price = $price['final_price_sum'];
                }
                $price['snapp_data'] = SnappPayService::checkSnappay($snapp_price);
            }
            return response()->json($price);
        } catch (\Exception $err) {
            $basket = BasketService::findBasketAndCheckStock();
            $basket->update(['shipping_method_id' => null]);
            return response()->json(['error' => $err->getMessage()], 500);
        }
    }

    // ایجاد سفارش و ارجاع به درگاه پرداخت
    public function create(Request $request)
    {
        if ($request->get('bank_id') == null) {
            return redirect()->back()->with('error', 'درگاه پرداخت مورد نظر خود را انتخاب کنید');
        }
        $basket = BasketService::findBasketAndCheckStock();
        $basket->update(['bank_id' => $request->get('bank_id')]);
        try {
            $order = $this->orderService->create($basket, $request->get('user_description'));
            return $this->orderService->checkout($order);
        } catch (\Exception $err) {
            \Log::info($err->getMessage());
            return redirect(route('basket.failed'))
                ->with('error', 'خطا در پرداخت، مجدد تلاش نمایید.');
        }
    }

    // ==================== Callback های درگاه‌های پرداخت ====================

    public function finishZarinPal(Request $request)
    {
        $input = $request->all();
        return $this->orderService->callback('transaction_info->post->Authority', $input['Authority'], $input);
    }

    public function finishSaman(Request $request)
    {
        $input = $request->all();
        return $this->orderService->callback('transaction_info->post->token', $input['Token'], $input);
    }

    public function finishSadad(Request $request)
    {
        $input = $request->all();
        return $this->orderService->callback('id', $input['OrderId'], $input);
    }

    public function finishSnappPay(Request $request)
    {
        $input = $request->all();
        return $this->orderService->callback('transaction_info->post->transactionId', $input['transactionId'], $input);
    }

    public function finishSaderat(Request $request)
    {
        $input = $request->all();
        $InvoiceID = $input['invoiceid'] ?? $input['InvoiceID'] ?? null;
        return $this->orderService->callback('transaction_info->post->invoiceid', $InvoiceID, $input);
    }

    public function finishIrDargah(Request $request)
    {
        $input = $request->all();
        return $this->orderService->callback('transaction_info->post->Authority', $input['authority'], $input);
    }

    public function finishParsian(Request $request)
    {
        $input = $request->all();
        return $this->orderService->callback('transaction_info->post->token', $input['Token'], $input);
    }

    public function finishZibal(Request $request)
    {
        $input = $request->all();
        return $this->orderService->callback('transaction_info->post->trackId', $input['trackId'], $input);
    }

    // ==================== صفحات نتیجه ====================

    public function success($id)
    {
        $order = OrderService::findById($id);
        if ($order->user_id != Auth::id()) {
            return redirect('/')->with('error', 'این فاکتور متعلق به شما نیست');
        }
        if ($order->order_status != "paid") {
            return redirect('/')->with('info', 'برای اطلاعات بیشتر با پشتیبانی تماس بگیرید');
        }
        return view('pages.cart.success', compact('order'));
    }

    public function failed()
    {
        return view('pages.cart.failed');
    }

    public function successDeposit($id)
    {
        $order = OrderService::findById($id);
        if ($order->user_id != Auth::id()) {
            return redirect('/')->with('error', 'این فاکتور متعلق به شما نیست');
        }
        if ($order->order_status != "deposit_paid") {
            return redirect('/')->with('info', 'برای اطلاعات بیشتر با پشتیبانی تماس بگیرید');
        }
        return view('pages.cart.upload-images', compact('order'));
    }
}
```

---

## ۲.۴ BasketService (سرویس سبد خرید)

```php
<?php
// Modules/Order/Services/BasketService.php

namespace Rahweb\CmsCore\Modules\Order\Services;

use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Order\Entities\Basket;
use Rahweb\CmsCore\Modules\Order\Entities\BasketItem;
use Rahweb\CmsCore\Modules\Order\Library\ChaparShipment;
use Rahweb\CmsCore\Modules\Product\Entities\Product;
use Rahweb\CmsCore\Modules\Product\Entities\ProductVariant;

class BasketService
{
    /**
     * ایجاد یا بروزرسانی سبد خرید
     */
    public static function create($product_id, $product_variant_id, $quantity, $cart = false)
    {
        $basket = Basket::authUser()->orderBy('id', 'DESC')->first();
        if (!$basket) {
            $basket = Basket::create([
                'user_id' => @\Auth::id(),
                'user_cookie' => session()->get('custom_data'),
            ]);
        }
        return self::checkValidity($product_id, $product_variant_id, $quantity, $basket, $cart);
    }

    /**
     * بررسی موجودی و قیمت محصول
     */
    public static function checkValidity($product_id, $product_variant_id, $quantity, $basket, $cart)
    {
        $product = Product::find($product_id);
        if ($product->stock == 0) {
            return response()->json([
                'success' => false,
                'button' => false,
                'message' => 'محصول مورد نظر موجود نمی باشد.',
            ], 200);
        }
        $item = null;
        if (count($product->variants) > 0) {
            if (@$product_variant_id == null) {
                return response()->json([
                    'success' => false,
                    'button' => false,
                    'swal' => true,
                    'message' => 'لطفا محصول مورد نظر خود را انتخاب کنید ',
                ], 200);
            }
            $item = BasketItem::whereBasketId($basket->id)
                ->whereProductId($product->id)
                ->where('product_variant_id', $product_variant_id)
                ->first();
            $variant = ProductVariant::find($product_variant_id);
            $stock = $variant ? $variant->stock : 0;
        } else {
            $item = BasketItem::whereBasketId($basket->id)
                ->whereProductId($product->id)
                ->first();
            $stock = $product ? $product->stock : 0;
        }
        $item_quantity = $item ? intval($item->quantity) : 0;

        if (intval($quantity) > 0) {
            if (intval($product->final_price) != 0) {
                if ($cart) {
                    $main_quantity = intval($quantity);
                } else {
                    $main_quantity = intval($quantity) + $item_quantity;
                }
                if ($stock >= $main_quantity) {
                    self::addItems($product_id, $product_variant_id, $main_quantity, $basket);
                } else {
                    return response()->json([
                        'success' => false,
                        'button' => false,
                        'swal' => true,
                        'message' => 'موجودی انبار کافی نیست',
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'button' => false,
                    'message' => 'محصول فاقد قیمت میباشد',
                ], 200);
            }
        }
    }

    /**
     * افزودن آیتم به سبد
     */
    public static function addItems($product_id, $product_variant_id, $quantity, $basket)
    {
        BasketItem::updateOrCreate(
            [
                'product_id' => $product_id,
                'product_variant_id' => $product_variant_id,
                'basket_id' => $basket->id,
            ],
            ['quantity' => $quantity]
        );
        return response()->json([
            'message' => 'محصول با موفقیت به سبد خرید اضافه شد.',
        ], 200);
    }

    /**
     * پیدا کردن سبد و بررسی موجودی
     */
    public static function findBasketAndCheckStock()
    {
        $basket = Basket::authUser()->orderBy('id', 'DESC')->first();
        if ($basket) {
            foreach ($basket->items as $item) {
                if (@$item->product == null) {
                    $item->delete();
                }
                if ($item->product_variant_id != null) {
                    if ($item->productVariant == null || $item->productVariant->stock == 0
                        || ($item->quantity > $item->productVariant->stock)) {
                        $item->delete();
                    }
                    if ($item->productVariant == null || $item->productVariant->final_price == 0) {
                        $item->delete();
                    }
                } else {
                    if ($item->product == null || $item->product->stock == 0
                        || ($item->quantity > $item->product->stock)) {
                        $item->delete();
                    }
                    if ($item->product == null || $item->product->final_price == 0) {
                        $item->delete();
                    }
                }
                if (!isset($basket->discount)) {
                    $basket->update(['discount_id' => null]);
                }
                $basket->save();
            }
            if (count(@$basket->items) == 0) {
                $basket->delete();
            }
        }
        return $basket;
    }

    /**
     * حذف کل سبد
     */
    public static function delete()
    {
        $basket = Basket::authUser()->orderBy('id', 'DESC')->first();
        if ($basket) {
            $basket->items()->delete();
            $basket->delete();
        }
    }

    public static function removeItem($itemId)
    {
        BasketItem::destroy($itemId);
    }

    /**
     * قیمت لیست سبد (صفحه سبد)
     */
    public static function listPrice()
    {
        $basket = Basket::authUser()->orderBy('id', 'DESC')->first();
        $final_price_sum = 0;
        $price_sum = 0;
        if ($basket && count($basket->items) > 0) {
            foreach ($basket->items as $item) {
                $final_price = $item->product_variant_id
                    ? $item->productVariant->final_price
                    : $item->product->final_price;
                $final_price_quantity = $final_price * $item->quantity;
                $price = @$item->productVariant
                    ? $item->productVariant->price
                    : $item->product->price;
                $price_quantity = $price * $item->quantity;
                $final_price_sum += $final_price_quantity;
                $price_sum += $price_quantity;
            }
        }
        $price_discount = $price_sum - $final_price_sum;
        return ['final_price_sum' => $final_price_sum, 'price_sum' => $price_sum, 'price_discount' => $price_discount];
    }

    /**
     * قیمت آدرس (صفحه انتخاب آدرس + ارسال)
     */
    public static function addressPrice()
    {
        $basket = Basket::authUser()->orderBy('id', 'DESC')->first();
        $final_price_sum = 0;
        $price_sum = 0;
        $weight_sum = 0;
        foreach ($basket->items as $item) {
            $final_price = $item->product_variant_id
                ? $item->productVariant->final_price
                : $item->product->final_price;
            $final_price_quantity = $final_price * $item->quantity;
            $price = $item->product_variant_id
                ? (intval($item->productVariant->discounted_price) != 0 ? $item->productVariant->price : 0)
                : (intval($item->product->discounted_price) != 0 ? $item->product->price : 0);
            $price_quantity = $price * $item->quantity;
            $final_price_sum += $final_price_quantity;
            $price_sum += $price_quantity;
            $weight = $item->product_variant_id
                ? $item->productVariant->weight
                : $item->product->weight;
            $weight_quantity = $weight * $item->quantity;
            $weight_sum += $weight_quantity;
            if (@$basket->shippingMethod->type == "with_weight") {
                $weight_sum = ceil($weight_sum / 100) * 100;
            }
        }
        $price_discount = $price_sum - $final_price_sum;
        $address = @$basket->address;
        $price_shipping = self::shipmentMethodPrice($basket->shippingMethod, $weight_sum, $final_price_sum, $address)['price'];
        $freight_balance = self::shipmentMethodPrice($basket->shippingMethod, $weight_sum, $final_price_sum, $address)['freight_balance'];
        $price_cart = (!empty($price_shipping) && is_numeric($price_shipping))
            ? intval($final_price_sum) + $price_shipping
            : $final_price_sum;
        return [
            'final_price_sum' => $final_price_sum,
            'price_sum' => $price_sum,
            'price_discount' => $price_discount,
            'price_shipping' => $price_shipping,
            'price_cart' => $price_cart,
            'freight_balance' => $freight_balance
        ];
    }

    /**
     * محاسبه هزینه ارسال
     */
    public static function calculateShipping(Basket $basket)
    {
        if (!$basket || !$basket->shippingMethod) {
            return 0;
        }
        $weight_sum = 0;
        foreach ($basket->items as $item) {
            $weight = $item->product_variant_id
                ? $item->productVariant->weight
                : $item->product->weight;
            $weight_sum += $weight * $item->quantity;
        }
        if ($basket->shippingMethod->type === "with_weight") {
            $weight_sum = ceil($weight_sum / 100) * 100;
        }
        $shipment = self::shipmentMethodPrice($basket->shippingMethod, $weight_sum, 0, $basket->address);
        return $shipment['price'] ?? 0;
    }

    /**
     * محاسبه قیمت بر اساس نوع روش ارسال
     * 3 نوع: fixed_price | with_weight | chapar (API)
     */
    public static function shipmentMethodPrice($shippingMethod, $weight_sum, $final_price_sum, $address)
    {
        $price = "";
        $freight_balance = 0;
        if (!$shippingMethod) {
            return $price;
        } else {
            if ($shippingMethod->type == "fixed_price") {
                $price = @$shippingMethod->price;
            } elseif ($shippingMethod->type == "with_weight") {
                // به ازای هر صد گرم قیمت گذاری شده
                $price = ($weight_sum / 100) * $shippingMethod->price;
            } else {
                // چاپار - وزن به کیلو، قیمت به ریال
                $response = ChaparShipment::quote([
                    'origin' => @$shippingMethod->sender_city->chapar_id,
                    'destination' => @$address->city->chapar_id,
                    'weight' => $weight_sum / 1000,
                    'value' => $final_price_sum . '0',
                    'method' => $shippingMethod->chapar_type,
                ]);
                // قیمت به تومان
                if ($response['result'] === true) {
                    $price = ($response['objects']['order']['price']['fld_Total_Cost']) / 10 ?? null;
                } else {
                    throw new \InvalidArgumentException($response['message']);
                }
            }
            if ($shippingMethod->freight_balance == 1) {
                $price = 0;
                $freight_balance = 1;
            }
            if ($shippingMethod->price_ceiling != 0 && $final_price_sum > $shippingMethod->price_ceiling) {
                $price = 0;
            }
        }
        return ['price' => $price, 'freight_balance' => $freight_balance];
    }

    /**
     * بررسی قیمت ارسال بر اساس آدرس
     */
    public static function checkShipmentPriceBaseOnAddress($address)
    {
        $basket = Basket::authUser()->orderBy('id', 'DESC')->first();
        $customShipments = [];
        foreach (@$address->city->availableShippingMethods as $shipment) {
            $weight_sum = 0;
            foreach ($basket->items as $item) {
                $weight = $item->product_variant_id
                    ? $item->productVariant->weight
                    : $item->product->weight;
                $weight_quantity = $weight * $item->quantity;
                $weight_sum += $weight_quantity;
                if ($shipment->type == "with_weight") {
                    $weight_sum = ceil($weight_sum / 100) * 100;
                }
            }
            if ($shipment->type == "with_weight" || $shipment->type == "fixed_price") {
                $price = self::shipmentMethodPrice($shipment, $weight_sum, 0, $address)['price'];
            } else {
                $price = "not_custom";
            }
            $customShipments[] = [
                'id' => $shipment->id,
                'title' => $shipment->title,
                'type' => $shipment->type,
                'freight_balance' => $shipment->freight_balance,
                'description' => $shipment->description,
                'price' => $price,
            ];
        }
        return $customShipments;
    }

    /**
     * ادغام سبد کوکی با سبد کاربر لاگین شده
     */
    public static function findUserBasketAndUpdateBaskets()
    {
        $currentBasket = Basket::cookieUser()
            ->whereNotNull('user_cookie')
            ->whereNull('user_id')
            ->first();
        $currentBaskets = Basket::orderBy('id', 'DESC')
            ->where('user_id', Auth::id())
            ->where('id', '<>', @$currentBasket->id)
            ->get();
        if ($currentBasket) {
            foreach ($currentBaskets as $row) {
                $row->delete();
            }
            $currentBasket->update(['user_id' => Auth::id()]);
        }
        return;
    }
}
```
---

# ۳. پرداخت

## ۳.۱ درگاه‌های بانکی پشتیبانی شده

این پروژه از **۸ درگاه پرداخت** پشتیبانی می‌کند:

| درگاه | bank_type | نوع اتصال |
|-------|-----------|-----------|
| زرین‌پال | zarinPal | REST API |
| سامان (sep) | sep | REST API |
| سداد | sadad | REST API + رمزنگاری PKCS7 |
| اسنپ‌پی | snappay | REST API + OAuth |
| سپهر (سادرات) | saderat | REST API |
| ایران درگاه | irandargah | REST API |
| پارسیان (سینا) | parsian | SOAP |
| زیبال | zibal | REST API |

---

## ۳.۲ رابط PaymentGateway

```php
<?php
// Modules/Order/Library/PaymentGateway.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Rahweb\CmsCore\Modules\Order\Entities\Order;

interface PaymentGateway
{
    public function getBankToken($amount, Order $order): PaymentPostDTO;
    public function verifyTransaction(array $callbackData, int $price, Order $order): PaymentVerifyDTO;
    public function redirect(PaymentPostDTO $postData, Order $order);
}
```

---

## ۳.۳ DTO های پرداخت

### PaymentPostDTO

```php
<?php
// Modules/Order/Library/PaymentPostDTO.php

namespace Rahweb\CmsCore\Modules\Order\Library;

class PaymentPostDTO
{
    protected string $status;
    protected array $post_data;

    public function getStatus()
    {
        return $this->status;
    }

    public function getPostData()
    {
        return $this->post_data;
    }

    public static function fromData($status, $post_data = [])
    {
        $self = new self();
        $self->status = $status;
        $self->post_data = $post_data;
        return $self;
    }
}
```

### PaymentVerifyDTO

```php
<?php
// Modules/Order/Library/PaymentVerifyDTO.php

namespace Rahweb\CmsCore\Modules\Order\Library;

class PaymentVerifyDTO
{
    protected string $status;
    protected array $verify_data;

    public function getStatus()
    {
        return $this->status;
    }

    public function getVerifyData()
    {
        return $this->verify_data;
    }

    public static function fromData($status, $verify_data = [])
    {
        $self = new self();
        $self->status = $status;
        $self->verify_data = $verify_data;
        return $self;
    }
}
```

### PaymentCancelDTO

```php
<?php
// Modules/Order/Library/PaymentCancelDTO.php

namespace Rahweb\CmsCore\Modules\Order\Library;

class PaymentCancelDTO
{
    protected string $successful;
    protected array $errorData;

    public function getSuccessful()
    {
        return $this->successful;
    }

    public function getErrorData()
    {
        return $this->errorData;
    }

    public static function fromData($successful, $errorData = [])
    {
        $self = new self();
        $self->successful = $successful;
        $self->errorData = $errorData;
        return $self;
    }
}
```

---

## ۳.۴ PaymentGatewayFactory

```php
<?php
// Modules/Order/Library/PaymentGatewayFactory.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Rahweb\CmsCore\Modules\Order\Entities\Bank;

class PaymentGatewayFactory
{
    public static function create(Bank $bank, string $site_name): PaymentGateway
    {
        return match ($bank->bank_type) {
            'zarinPal' => new ZarinPal($bank, $site_name),
            'sep' => new SepSaman($bank, $site_name),
            'sadad' => new Sadad($bank, $site_name),
            'snappay' => new SnappPay($bank, $site_name),
            'saderat' => new Saderat($bank, $site_name),
            'irandargah' => new IranDargah($bank, $site_name),
            'parsian' => new Parsian($bank, $site_name),
            'zibal' => new Zibal($bank, $site_name),
            default => throw new \InvalidArgumentException("Unsupported bank type: {$bank->bank_type}")
        };
    }
}
```

---

## ۳.۵ درگاه زرین‌پال

```php
<?php
// Modules/Order/Library/ZarinPal.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Illuminate\Support\Facades\Http;
use Rahweb\CmsCore\Modules\Order\Entities\Order;
use Rahweb\CmsCore\Modules\Order\Jobs\ZarinpalInquiryJob;

class ZarinPal implements PaymentGateway
{
    private $merchant;
    private $site_name;
    private const EP_REQUEST = 'https://payment.zarinpal.com/pg/v4/payment/request.json';
    private const EP_VERIFY = 'https://payment.zarinpal.com/pg/v4/payment/verify.json';
    private const EP_INQUIRY = 'https://payment.zarinpal.com/pg/v4/payment/inquiry.json';
    private const EP_REDIRECT = 'https://payment.zarinpal.com/pg/StartPay/';
    private const CALLBACK_ROUTE_NAME = 'basket.finish.zarin-pal';
    private const USER_AGENT = 'ZarinPal Rest Api v1';

    public function __construct($bank, $site_name)
    {
        $config = json_decode(@$bank['config'] ?? '[]', true);
        $this->merchant = $config['MerchantId'] ?? null;
        $this->site_name = $site_name;
    }

    public function getBankToken($amount, $order): PaymentPostDTO
    {
        $amount = intval($amount . "0");
        $description = $this->site_name . ' خرید از ';

        $payload = [
            'merchant_id' => $this->merchant,
            'amount' => $amount,
            'callback_url' => route(self::CALLBACK_ROUTE_NAME),
            'description' => $description,
            'metadata' => []
        ];

        $resp = $this->sendJsonRequest(self::EP_REQUEST, $payload, 'POST');
        if (!$resp['ok']) {
            return PaymentPostDTO::fromData('failed');
        }

        $result = $resp['json'];
        if (isset($result['data']['code']) && (int)$result['data']['code'] === 100) {
            return PaymentPostDTO::fromData('success', ['Authority' => $result['data']['authority'] ?? null]);
        }

        return PaymentPostDTO::fromData('failed');
    }

    public function verifyTransaction($callbackData, $price, $order = null): PaymentVerifyDTO
    {
        $amount = intval($price . "0");
        if ($callbackData['Status'] !== "OK") {
            return PaymentVerifyDTO::fromData('failed');
        }

        $payload = [
            'merchant_id' => $this->merchant,
            'authority' => $callbackData['Authority'],
            'amount' => $amount,
        ];

        $resp = $this->sendJsonRequest(self::EP_VERIFY, $payload, 'POST');
        if (!$resp['ok']) {
            return PaymentVerifyDTO::fromData('failed');
        }

        $result = $resp['json'];
        if (isset($result['data']['code']) && ((int)$result['data']['code'] === 100 || (int)$result['data']['code'] === 101)) {
            return PaymentVerifyDTO::fromData('success', ['RefID' => $result['data']['ref_id'] ?? null]);
        }

        return PaymentVerifyDTO::fromData('failed');
    }

    public function inquiry($authority)
    {
        $payload = [
            'merchant_id' => $this->merchant,
            'authority' => $authority,
        ];
        $resp = $this->sendJsonRequest(self::EP_INQUIRY, $payload, 'POST');
        if (!$resp['ok']) {
            return ['status' => 'failed'];
        }
        $result = $resp['json'];
        if (isset($result['data']) && isset($result['data']['status'])) {
            $status = strtoupper((string)$result['data']['status']);
            if ($status === 'VERIFIED' || $status === 'PAID') {
                return ['status' => 'success', 'RefID' => $result['data']['authority'] ?? null];
            }
        }
        return ['status' => 'failed'];
    }

    public function redirect(PaymentPostDTO $postData, Order $order): void
    {
        ZarinpalInquiryJob::dispatch($order->id, $this->site_name)
            ->onConnection('default_mysql')
            ->delay(now()->addMinutes(15));

        $authority = $postData->getPostData()['Authority'];
        header('Location: ' . self::EP_REDIRECT . $authority);
        exit;
    }

    private function sendJsonRequest(string $url, array $payload = [], string $method = 'POST'): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => self::USER_AGENT,
            ])->timeout(30)->send($method, $url, ['json' => $payload]);

            if ($response->failed()) {
                return ['ok' => false, 'json' => null];
            }
            $json = $response->json();
            if (!is_array($json)) {
                return ['ok' => false, 'json' => null];
            }
            return ['ok' => true, 'json' => $json];
        } catch (\Throwable $e) {
            return ['ok' => false, 'json' => null];
        }
    }
}
```

---

## ۳.۶ درگاه سامان (Sep)

```php
<?php
// Modules/Order/Library/SepSaman.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Illuminate\Support\Facades\Http;
use Rahweb\CmsCore\Modules\Order\Entities\Order;

class SepSaman implements PaymentGateway
{
    private $TerminalId;
    private $site_name;
    private const EP_TOKEN = 'https://sep.shaparak.ir/onlinepg/onlinepg';
    private const EP_VERIFY = 'https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction';
    private const EP_REDIRECT_BASE = 'https://sep.shaparak.ir/OnlinePG/SendToken';
    private const CALLBACK_ROUTE_NAME = 'basket.finish.saman-bank';
    private const USER_AGENT = 'SepSaman Rest Api v1';

    public function __construct($bank, $site_name)
    {
        $config = json_decode(@$bank['config'] ?? '[]', true);
        $this->TerminalId = $config['TerminalId'] ?? null;
        $this->site_name = $site_name;
    }

    public function getBankToken($amount, Order $order): PaymentPostDTO
    {
        $amount = intval($amount . "0");
        $payload = [
            "action" => "token",
            "TerminalId" => $this->TerminalId,
            "Amount" => $amount,
            "ResNum" => $order->id,
            "RedirectUrl" => route(self::CALLBACK_ROUTE_NAME),
            "CellNumber" => $order->user->mobile,
        ];

        $resp = $this->sendJsonRequest(self::EP_TOKEN, $payload, 'POST');
        if (!$resp['ok']) {
            return PaymentPostDTO::fromData('failed');
        }
        $response = $resp['json'];
        if (@$response["status"] != 1) {
            return PaymentPostDTO::fromData('failed');
        }
        return PaymentPostDTO::fromData('success', ['token' => $response['token'] ?? null]);
    }

    public function verifyTransaction($callbackData, $price, $order = null): PaymentVerifyDTO
    {
        if (@$callbackData['State'] == 'CanceledByUser') {
            return PaymentVerifyDTO::fromData('failed', [
                "RefNum" => $callbackData["RefNum"],
                "TraceNo" => $callbackData["TraceNo"],
            ]);
        }

        $params = [
            'TerminalNumber' => $this->TerminalId,
            'RefNum' => $callbackData['RefNum'],
        ];
        $this->sendJsonRequest(self::EP_VERIFY, $params, 'POST');

        if ($callbackData['Status'] == 2) {
            return PaymentVerifyDTO::fromData('success', [
                "RefNum" => $callbackData["RefNum"],
                "TraceNo" => $callbackData["TraceNo"],
            ]);
        } else {
            return PaymentVerifyDTO::fromData('failed', [
                "RefNum" => $callbackData["RefNum"],
                "TraceNo" => $callbackData["TraceNo"],
            ]);
        }
    }

    public function redirect(PaymentPostDTO $postData, Order $order): void
    {
        $token = $postData->getPostData()['token'];
        $url = self::EP_REDIRECT_BASE . '?token=' . $token;
        header("Location: $url");
        exit;
    }

    private function sendJsonRequest(string $url, array $payload = [], string $method = 'POST'): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => self::USER_AGENT,
            ])->timeout(30)->send($method, $url, ['json' => $payload]);

            if ($response->failed()) {
                return ['ok' => false, 'json' => null];
            }
            $json = $response->json();
            if (!is_array($json)) {
                return ['ok' => false, 'json' => null];
            }
            return ['ok' => true, 'json' => $json];
        } catch (\Throwable $e) {
            return ['ok' => false, 'json' => null];
        }
    }
}
```

---

## ۳.۷ درگاه سداد

```php
<?php
// Modules/Order/Library/Sadad.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Illuminate\Support\Facades\Http;
use Rahweb\CmsCore\Modules\Order\Entities\Order;

class Sadad implements PaymentGateway
{
    private $key;
    private $TerminalId;
    private $merchant;
    private $site_name;
    private const EP_REQUEST  = 'https://sadad.shaparak.ir/vpg/api/v0/Request/PaymentRequest';
    private const EP_VERIFY   = 'https://sadad.shaparak.ir/vpg/api/v0/Advice/Verify';
    private const EP_REDIRECT = 'https://sadad.shaparak.ir/VPG/Purchase';
    private const CALLBACK_ROUTE_NAME = 'basket.finish.sadad-bank';
    private const USER_AGENT  = 'Sadad Rest Api v1';

    public function __construct($bank, $site_name)
    {
        $config = json_decode(@$bank['config'] ?? '[]', true);
        $this->merchant   = $config['MerchantId'] ?? null;
        $this->key        = $config['key'] ?? null;
        $this->TerminalId = $config['TerminalId'] ?? null;
        $this->site_name  = $site_name;
    }

    public function getBankToken($amount, $order): PaymentPostDTO
    {
        $amount        = intval($amount . "0");
        $OrderId       = $order->id;
        $TerminalId    = $this->TerminalId;
        $key           = $this->key;
        $merchant      = $this->merchant;
        $LocalDateTime = date("m/d/Y g:i:s a");
        $ReturnUrl     = route(self::CALLBACK_ROUTE_NAME);

        $SignData = self::encrypt_pkcs7("$TerminalId;$OrderId;$amount", "$key");

        $data = [
            'TerminalId'    => $TerminalId,
            'MerchantId'    => $merchant,
            'Amount'        => $amount,
            'SignData'      => $SignData,
            'ReturnUrl'     => $ReturnUrl,
            'LocalDateTime' => $LocalDateTime,
            'OrderId'       => $OrderId,
        ];

        $result = self::CallAPI(self::EP_REQUEST, $data);
        if (@$result['ResCode'] != 0) {
            return PaymentPostDTO::fromData('failed');
        }
        return PaymentPostDTO::fromData('success', ['token' => $result['Token']]);
    }

    public function verifyTransaction($callbackData, $price = null, $order = null): PaymentVerifyDTO
    {
        $key = $this->key;
        $verifyData = [
            'Token'    => $callbackData['token'],
            'SignData' => self::encrypt_pkcs7($callbackData['token'], $key),
        ];
        $result = self::CallAPI(self::EP_VERIFY, $verifyData);

        if ($result && ($result['ResCode'] != -1 && $result['ResCode'] == 0 || $result['ResCode'] == 100)) {
            return PaymentVerifyDTO::fromData('success', [
                'RetrivalRefNo' => $result['RetrivalRefNo'],
                'SystemTraceNo' => $result['SystemTraceNo'],
            ]);
        }
        return PaymentVerifyDTO::fromData('failed');
    }

    public static function encrypt_pkcs7($str, $key)
    {
        $key = base64_decode($key);
        $ciphertext = OpenSSL_encrypt($str, "DES-EDE3", $key, OPENSSL_RAW_DATA);
        return base64_encode($ciphertext);
    }

    public static function CallAPI($url, $data = false)
    {
        try {
            $request = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
                'User-Agent'   => self::USER_AGENT,
            ])->withOptions(['verify' => false])->timeout(30);

            $response = $request->send('POST', $url, [
                'json' => $data ?: new \stdClass(),
            ]);
            $body = $response->body();
            return !empty($body) ? json_decode($body, true) : false;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function redirect(PaymentPostDTO $postData, Order $order): void
    {
        $token = $postData->getPostData()['token'];
        $url   = self::EP_REDIRECT;
        echo "
            <form name='myform' action='{$url}' method='GET'>
                <input type='hidden' id='Token' name='Token' value='{$token}'>
            </form>
            <script>window.onload = function() { document.forms[0].submit(); }</script>
        ";
        exit;
    }
}
```

---

## ۳.۸ درگاه اسنپ‌پی

```php
<?php
// Modules/Order/Library/SnappPay.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Rahweb\CmsCore\Modules\Order\Entities\Order;

class SnappPay implements PaymentGateway
{
    private string $base_url = 'https://api.snapppay.ir';
    private $ep_oauth_token = '/api/online/v1/oauth/token';
    private $ep_eligible = '/api/online/offer/v1/eligible';
    private $ep_request = '/api/online/payment/v1/token';
    private $ep_verify = '/api/online/payment/v1/verify';
    private $ep_settle = '/api/online/payment/v1/settle';
    private $ep_cancel = '/api/online/payment/v1/cancel';
    private $ep_update = '/api/online/payment/v1/update';
    private $ep_inquiry = '/api/online/payment/v1/status';
    private const FAILED_STATUSES = ["INIT", "REGISTERED", "REVERT", "CANCEL"];
    private const CALLBACK_ROUTE_NAME = 'basket.finish.snapp-pay';

    private ?string $client_id = null;
    private ?string $client_secret = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $access_token = null;

    public function __construct($bank, $site_name)
    {
        if ($bank && isset($bank['config'])) {
            $cfg = is_array($bank['config']) ? $bank['config'] : json_decode($bank['config'], true);
            if (!empty($cfg['base_url'])) {
                $this->base_url = rtrim($cfg['base_url'], '/');
            }
            $this->client_id = $cfg['client_id'] ?? null;
            $this->client_secret = $cfg['client_secret'] ?? null;
            $this->username = $cfg['username'] ?? null;
            $this->password = $cfg['password'] ?? null;
            $this->site_name = $site_name;
            $this->ep_oauth_token = $this->prepareUrl($cfg['ep_oauth_token'] ?? $this->ep_oauth_token);
            $this->ep_eligible = $this->prepareUrl($cfg['ep_eligible'] ?? $this->ep_eligible);
            $this->ep_request = $this->prepareUrl($cfg['ep_request'] ?? $this->ep_request);
            $this->ep_verify = $this->prepareUrl($cfg['ep_verify'] ?? $this->ep_verify);
            $this->ep_settle = $this->prepareUrl($cfg['ep_settle'] ?? $this->ep_settle);
            $this->ep_cancel = $this->prepareUrl($cfg['ep_cancel'] ?? $this->ep_cancel);
            $this->ep_update = $this->prepareUrl($cfg['ep_update'] ?? $this->ep_update);
            $this->ep_inquiry = $this->prepareUrl($cfg['ep_inquiry'] ?? $this->ep_inquiry);
        }
        $this->getBearerToken();
    }

    private function prepareUrl(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }
        return $this->base_url . '/' . ltrim($url, '/');
    }

    public function getBankToken($amount, Order $order): PaymentPostDTO
    {
        try {
            $price = str_replace(',', '', $order->deposit_price) != 0
                ? str_replace(',', '', $order->deposit_price)
                : str_replace(',', '', $order->payment_price);
            if (!$this->checkEligibleAmount($price) || @$this->checkEligibleAmount($price)['response']['eligible'] != true) {
                return PaymentPostDTO::fromData('failed');
            }
            $txId = $this->generateUniqueTxId();
            $orderItems = $order->items;
            $cartItemsArray = [];
            $totalAmount = 0;

            foreach ($orderItems as $item) {
                $item_amount = intval($item->discounted_price) != 0
                    ? intval(($item->discounted_price * 10))
                    : intval(($item->price * 10));
                $cartItemsArray[] = [
                    'amount' => $item_amount,
                    'category' => @$item->product->categories[0]->title,
                    'count' => $item->quantity,
                    'id' => $item->product_id,
                    'name' => @$item->product->title,
                    'commissionType' => '100',
                ];
                $totalAmount += $item_amount * $item->quantity;
            }

            $amountRial = intval($price * 10);
            $cartListJson = [[
                'cartId' => $order->id . '13941364',
                'cartItems' => $cartItemsArray,
                'isShipmentIncluded' => true,
                'isTaxIncluded' => true,
                'shippingAmount' => (intval($order->shipping_price * 10)),
                'taxAmount' => 0,
                'totalAmount' => intval($totalAmount)
            ]];
            $firstNinePosition = strpos($order->user->mobile, '9');
            $mobile = substr($order->user->mobile, $firstNinePosition);
            $userMobile = '+98' . $mobile;

            $payload = [
                "amount" => $amountRial,
                "cartList" => $cartListJson,
                "discountAmount" => (intval($order->discount_price * 10)),
                "externalSourceAmount" => 0,
                "mobile" => $userMobile,
                "paymentMethodTypeDto" => "INSTALLMENT",
                "returnURL" => route(self::CALLBACK_ROUTE_NAME),
                "transactionId" => $txId,
            ];
            $response = Http::withToken($this->access_token)
                ->acceptJson()
                ->timeout(7200)
                ->post($this->ep_request, $payload)
                ->json();

            if (@$response['response']['paymentToken']) {
                return PaymentPostDTO::fromData('success', [
                    'paymentToken' => $response['response']['paymentToken'] ?? null,
                    'transactionId' => $txId,
                    'paymentPageUrl' => $response['response']['paymentPageUrl'] ?? null,
                ]);
            }
            return PaymentPostDTO::fromData('failed');
        } catch (\Throwable $e) {
            Log::error('SnappPay getBankToken error', ['message' => $e->getMessage()]);
            return PaymentPostDTO::fromData('failed');
        }
    }

    public function verifyTransaction(array $callbackData, int $price, Order $order): PaymentVerifyDTO
    {
        try {
            if (@$callbackData['state'] !== "OK") {
                return PaymentVerifyDTO::fromData('failed');
            }
            return $this->verify($order);
        } catch (\Throwable $e) {
            Log::error('SnappPay verifyTransaction error', ['message' => $e->getMessage()]);
            return PaymentVerifyDTO::fromData('failed');
        }
    }

    public function redirect(PaymentPostDTO $postData, Order $order)
    {
        header("refresh:0;url=" . $postData->getPostData()['paymentPageUrl']);
    }

    public function verify($order)
    {
        $info = json_decode($order->transaction_info, true);
        $paymentToken = $info['post']['paymentToken'] ?? null;
        if (!$paymentToken) {
            return PaymentVerifyDTO::fromData('failed');
        }
        try {
            $verifyResp = Http::withToken($this->access_token)
                ->acceptJson()->timeout(60)
                ->post($this->ep_verify, ['paymentToken' => $paymentToken])
                ->json();
        } catch (\Throwable $e) {
            $verifyResp = null;
        }
        $verifySuccessful = $verifyResp['successful'] ?? false;
        $verifyStatus = $verifyResp['response']['status'] ?? null;

        if (!$verifySuccessful || ($verifyStatus && in_array($verifyStatus, self::FAILED_STATUSES, true))) {
            $inq = $this->inquiry($paymentToken);
            if (!$inq || !($inq['successful'] ?? false)) {
                return PaymentVerifyDTO::fromData('failed');
            }
            $inqStatus = $inq['response']['status'] ?? null;
            if (in_array($inqStatus, ['VERIFY', 'PENDING'], true)) {
                return $this->verify($order);
            }
            if ($inqStatus === 'SETTLE') {
                return $this->settle($paymentToken);
            }
            if (in_array($inqStatus, self::FAILED_STATUSES, true)) {
                return PaymentVerifyDTO::fromData('failed');
            }
        }
        return $this->settle($paymentToken);
    }

    public function cancel(Order $order)
    {
        try {
            $transaction = json_decode($order->transaction_info, true);
            $paymentToken = $transaction['post']['paymentToken'];
            $cancelResp = Http::withToken($this->access_token)
                ->acceptJson()->timeout(7200)
                ->post($this->ep_cancel, ['paymentToken' => $paymentToken])
                ->json();
            if (@$cancelResp['successful'] == false) {
                return PaymentCancelDTO::fromData('false', $cancelResp['errorData']);
            }
            return PaymentCancelDTO::fromData('success');
        } catch (\Throwable $e) {
            return PaymentCancelDTO::fromData('failed');
        }
    }

    public function inquiry(string $paymentToken)
    {
        try {
            return Http::withToken($this->access_token)
                ->acceptJson()->timeout(15)
                ->get($this->ep_inquiry, ['paymentToken' => $paymentToken])
                ->json();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function settle(string $paymentToken)
    {
        try {
            $settleResp = Http::withToken($this->access_token)
                ->acceptJson()->timeout(20)
                ->post($this->ep_settle, ['paymentToken' => $paymentToken])
                ->json();
        } catch (\Throwable $e) {
            $settleResp = null;
        }
        $successful = $settleResp['successful'] ?? false;
        if (!$successful) {
            $inq = $this->inquiry($paymentToken);
            if (!$inq) {
                return PaymentVerifyDTO::fromData('failed');
            }
            $status = $inq['response']['status'] ?? null;
            if ($status === 'VERIFY') {
                return $this->settle($paymentToken);
            }
            if ($status === 'SETTLE') {
                return PaymentVerifyDTO::fromData('success');
            }
            if (in_array($status, self::FAILED_STATUSES, true)) {
                return PaymentVerifyDTO::fromData('failed');
            }
        }
        return PaymentVerifyDTO::fromData($successful ? 'success' : 'failed');
    }

    public function generateUniqueTxId()
    {
        do {
            $txId = 'tx_' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Order::where('transaction_info->post->transactionId', $txId)->exists());
        return $txId;
    }

    public function getBearerToken(): void
    {
        $basicKey = base64_encode("{$this->client_id}:{$this->client_secret}");
        try {
            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => 'Basic ' . $basicKey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])->timeout(60)
                ->post($this->ep_oauth_token, [
                    'grant_type' => 'password',
                    'scope' => 'online-merchant',
                    'username' => $this->username,
                    'password' => $this->password,
                ])->json();
            $this->access_token = $response['access_token'] ?? null;
        } catch (\Throwable $e) {
            $this->access_token = null;
        }
    }

    public function eligible($price)
    {
        return $this->checkEligibleAmount($price);
    }

    private function checkEligibleAmount($price)
    {
        try {
            $resp = Http::withToken($this->access_token)
                ->get($this->ep_eligible, ['amount' => $price * 10])
                ->json();
            return $resp;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
```

---

## ۳.۹ درگاه سپهر (سادرات)

```php
<?php
// Modules/Order/Library/Saderat.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Illuminate\Support\Facades\Http;
use Rahweb\CmsCore\Modules\Order\Entities\Order;

class Saderat implements PaymentGateway
{
    private string $terminalId;
    private string $site_name;
    private const EP_GET_TOKEN = 'https://sepehr.shaparak.ir/Rest/V1/PeymentApi/GetToken';
    private const EP_ADVICE    = 'https://sepehr.shaparak.ir/Rest/V1/PeymentApi/Advice';
    private const EP_REDIRECT  = 'https://sepehr.shaparak.ir/Payment/Pay';
    private const CALLBACK_ROUTE_NAME = 'basket.finish.saderat';
    private const USER_AGENT = 'Sepehr (Saderat) Rest Api v1';

    public function __construct($bank, $site_name)
    {
        $config = json_decode(@$bank['config'] ?? '[]', true);
        $this->terminalId = (string)($config['TerminalID'] ?? '');
        $this->site_name  = $site_name;
    }

    public function getBankToken($amount, Order $order): PaymentPostDTO
    {
        if (empty($this->terminalId)) {
            return PaymentPostDTO::fromData('failed');
        }
        $invoiceId = (string)($order->id ?? uniqid('inv_', true));
        $amount = intval($amount . "0");
        $payload = [
            'TerminalID'  => $this->terminalId,
            'Amount'      => (string)$amount,
            'InvoiceID'   => $invoiceId,
            'callbackURL' => route(self::CALLBACK_ROUTE_NAME),
            'payload'     => '',
        ];
        $resp = $this->sendJsonRequest(self::EP_GET_TOKEN, $payload, 'POST');
        if (!$resp['ok']) {
            return PaymentPostDTO::fromData('failed');
        }
        $json = $resp['json'];
        $token = $json['Accesstoken'] ?? $json['AccessToken'] ?? null;
        $statusOk = isset($json['Status']) && (string)$json['Status'] === '0';
        if ($statusOk && $token) {
            return PaymentPostDTO::fromData('success', ['token' => $token, 'invoiceid' => $invoiceId]);
        }
        return PaymentPostDTO::fromData('failed');
    }

    public function verifyTransaction(array $callbackData, int $price, Order $order): PaymentVerifyDTO
    {
        $respCode = (int)($callbackData['respcode'] ?? $callbackData['RespCode'] ?? -1);
        if ($respCode !== 0) {
            return PaymentVerifyDTO::fromData('failed');
        }
        $digitalReceipt = $callbackData['digitalreceipt'] ?? $callbackData['DigitalReceipt'] ?? null;
        if (!$digitalReceipt) {
            return PaymentVerifyDTO::fromData('failed');
        }
        $payload = [
            'digitalreceipt' => (string)$digitalReceipt,
            'Tid'            => $this->terminalId,
        ];
        $resp = $this->sendJsonRequest(self::EP_ADVICE, $payload, 'POST');
        if (!$resp['ok']) {
            return PaymentVerifyDTO::fromData('failed');
        }
        $json = $resp['json'];
        $status = strtoupper((string)($json['Status'] ?? ''));
        $returnId = (string)($json['ReturnId'] ?? '');
        $rial_price = $price . '0';
        $amountMatches = ($returnId !== '') && ((int)$returnId === (int)$rial_price);
        if (in_array($status, ['OK', 'DUPLICATE'], true) && $amountMatches) {
            $ref = $callbackData['rrn'] ?? $callbackData['RRN'] ?? $digitalReceipt;
            return PaymentVerifyDTO::fromData('success', ['RefID' => $ref]);
        }
        return PaymentVerifyDTO::fromData('failed');
    }

    public function redirect(PaymentPostDTO $postData, Order $order)
    {
        $data = $postData->getPostData();
        $token = $data['token'] ?? null;
        if (!$token) {
            abort(400, 'Payment token missing.');
        }
        $url = self::EP_REDIRECT . '?token=' . urlencode($token) . '&terminalId=' . urlencode($this->terminalId);
        header('Location: ' . $url);
        exit;
    }

    private function sendJsonRequest(string $url, array $payload = [], string $method = 'POST'): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => self::USER_AGENT,
            ])->timeout(30)->send($method, $url, ['json' => $payload]);
            if ($response->failed()) {
                return ['ok' => false, 'json' => null];
            }
            $json = $response->json();
            if (!is_array($json)) {
                return ['ok' => false, 'json' => null];
            }
            return ['ok' => true, 'json' => $json];
        } catch (\Throwable $e) {
            return ['ok' => false, 'json' => null];
        }
    }
}
```

---

## ۳.۱۰ درگاه ایران درگاه

```php
<?php
// Modules/Order/Library/IranDargah.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Illuminate\Support\Facades\Http;
use Rahweb\CmsCore\Modules\Order\Entities\Order;

class IranDargah implements PaymentGateway
{
    private $merchant;
    private $site_name;
    private const EP_REQUEST = 'https://dargaah.ir/payment';
    private const EP_VERIFY  = 'https://dargaah.ir/verification';
    private const EP_REDIRECT = 'https://dargaah.ir/ird/startpay/';
    private const CALLBACK_ROUTE_NAME = 'basket.finish.irandargah';

    public function __construct($bank, $site_name)
    {
        $config = json_decode(@$bank['config'] ?? '[]', true);
        $this->merchant = $config['MerchantId'] ?? 'TEST';
        $this->site_name = $site_name;
    }

    public function getBankToken($amount, $order): PaymentPostDTO
    {
        $amount = intval($amount . "0");
        $payload = [
            "merchantID"   => $this->merchant,
            "amount"       => $amount,
            "callbackURL"  => route(self::CALLBACK_ROUTE_NAME),
            "orderId"      => $order->id,
            "cardNumber"   => null,
        ];
        $resp = $this->sendJsonRequest(self::EP_REQUEST, $payload);
        if (!$resp['ok']) {
            return PaymentPostDTO::fromData('failed');
        }
        $result = $resp['json'];
        if ((int)$result['status'] === 200) {
            return PaymentPostDTO::fromData('success', ['Authority' => $result['authority']]);
        }
        return PaymentPostDTO::fromData('failed');
    }

    public function verifyTransaction($callbackData, $price, $order = null): PaymentVerifyDTO
    {
        if (!isset($callbackData['authority'])) {
            return PaymentVerifyDTO::fromData('failed');
        }
        $price = intval($price . "0");
        $payload = [
            "merchantID" => $this->merchant,
            "authority"  => $callbackData['authority'],
            "amount"     => intval($price),
            "orderId"    => $order?->id ?? null
        ];
        $resp = $this->sendJsonRequest(self::EP_VERIFY, $payload);
        if (!$resp['ok']) {
            return PaymentVerifyDTO::fromData('failed');
        }
        $result = $resp['json'];
        if ((int)$result['status'] === 100) {
            return PaymentVerifyDTO::fromData('success', ['RefID' => $result['refId']]);
        }
        return PaymentVerifyDTO::fromData('failed');
    }

    public function redirect(PaymentPostDTO $postData, Order $order): void
    {
        $authority = $postData->getPostData()['Authority'];
        header('Location: ' . self::EP_REDIRECT . $authority);
        exit;
    }

    private function sendJsonRequest(string $url, array $payload): array
    {
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->withOptions(['verify' => false])
                ->post($url, $payload);
            if ($response->failed()) {
                return ['ok' => false, 'json' => null];
            }
            return ['ok' => true, 'json' => $response->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'json' => null];
        }
    }
}
```

---

## ۳.۱۱ درگاه پارسیان (SOAP)

```php
<?php
// Modules/Order/Library/Parsian.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Rahweb\CmsCore\Modules\Order\Entities\Order;
use SoapClient;

class Parsian implements PaymentGateway
{
    private string $loginAccount;
    private string $site_name;
    private const EP_SALE_WSDL = 'https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?wsdl';
    private const EP_CONFIRM_WSDL = 'https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?wsdl';
    private const EP_REDIRECT = 'https://pec.shaparak.ir/NewIPG/?token=';
    private const CALLBACK_ROUTE_NAME = 'basket.finish.parsian';

    public function __construct($bank, $site_name)
    {
        $config = json_decode(@$bank['config'] ?? '[]', true);
        $this->loginAccount = (string)($config['LoginAccount'] ?? $config['pin'] ?? '');
        $this->site_name = $site_name;
    }

    public function getBankToken($amount, Order $order): PaymentPostDTO
    {
        if (empty($this->loginAccount)) {
            return PaymentPostDTO::fromData('failed');
        }
        $client = $this->makeSoapClient(self::EP_SALE_WSDL);
        if (!is_object($client)) {
            return PaymentPostDTO::fromData('failed');
        }
        $requestData = [
            'LoginAccount' => $this->loginAccount,
            'OrderId' => (int)$order->id,
            'Amount' => (int)$amount . '0',
            'CallBackUrl' => route(self::CALLBACK_ROUTE_NAME),
            'AdditionalData' => '',
            'Originator' => '',
        ];
        try {
            $response = $client->SalePaymentRequest(['requestData' => $requestData]);
            $result = $response->SalePaymentRequestResult ?? null;
            $status = isset($result->Status) ? (int)$result->Status : -1;
            $token = $result->Token ?? null;
            if ($status === 0 && !empty($token)) {
                return PaymentPostDTO::fromData('success', ['token' => $token]);
            }
        } catch (\Throwable $e) {
            // error
        }
        return PaymentPostDTO::fromData('failed');
    }

    public function verifyTransaction(array $callbackData, int $price, Order $order): PaymentVerifyDTO
    {
        $status = (int)($callbackData['status'] ?? $callbackData['Status'] ?? -1);
        $token = $callbackData['Token'] ?? $callbackData['token'] ?? null;
        $rrn = $callbackData['RRN'] ?? $callbackData['rrn'] ?? null;
        if ($status !== 0 || empty($token) || (int)$rrn <= 0) {
            return PaymentVerifyDTO::fromData('failed');
        }
        $client = $this->makeSoapClient(self::EP_CONFIRM_WSDL);
        if (!$client) {
            return PaymentVerifyDTO::fromData('failed');
        }
        $requestData = [
            'LoginAccount' => $this->loginAccount,
            'Token' => (int)$token,
        ];
        try {
            $response = $client->ConfirmPayment(['requestData' => $requestData]);
            $result = $response->ConfirmPaymentResult ?? null;
            $confirmStatus = isset($result->Status) ? (int)$result->Status : -1;
            $confirmRrn = $result->RRN ?? $rrn;
            if ($confirmStatus === 0) {
                return PaymentVerifyDTO::fromData('success', [
                    'RRN' => $confirmRrn,
                    'Token' => $token,
                    'CardNumberMasked' => $result->CardNumberMasked ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            // error
        }
        return PaymentVerifyDTO::fromData('failed');
    }

    public function redirect(PaymentPostDTO $postData, Order $order)
    {
        $token = $postData->getPostData()['token'] ?? null;
        if (empty($token)) {
            abort(400, 'Payment token missing.');
        }
        header('Location: ' . self::EP_REDIRECT . urlencode((string)$token));
        exit;
    }

    private function makeSoapClient(string $wsdl): ?SoapClient
    {
        try {
            return new SoapClient($wsdl, [
                'cache_wsdl' => WSDL_CACHE_NONE,
                'trace' => true,
                'exceptions' => true,
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
```

---

## ۳.۱۲ درگاه زیبال

```php
<?php
// Modules/Order/Library/Zibal.php

namespace Rahweb\CmsCore\Modules\Order\Library;

use Exception;
use Illuminate\Support\Facades\Http;
use Rahweb\CmsCore\Modules\Order\Entities\Order;

class Zibal implements PaymentGateway
{
    private $merchant;
    private $site_name;
    private const EP_REQUEST  = 'https://gateway.zibal.ir/v1/request';
    private const EP_VERIFY   = 'https://gateway.zibal.ir/v1/verify';
    private const EP_REDIRECT = 'https://gateway.zibal.ir/start/';
    private const CALLBACK_ROUTE_NAME = 'basket.finish.zibal-bank';
    private const USER_AGENT  = 'Zibal Rest Api v1';

    public function __construct($bank, $site_name)
    {
        $config = json_decode(@$bank['config'] ?? '[]', true);
        $this->merchant = $config['MerchantId'] ?? null;
        $this->site_name = $site_name;
    }

    public function getBankToken($amount, $order): PaymentPostDTO
    {
        $amount = intval($amount . "0");
        $data = [
            'merchant'    => $this->merchant,
            'amount'      => $amount,
            'callbackUrl' => route(self::CALLBACK_ROUTE_NAME),
            'description' => 'خرید از ' . $this->site_name,
            'orderId'     => $order->id,
        ];
        $result = self::CallAPI(self::EP_REQUEST, $data);
        if (!$result || !isset($result['result']) || $result['result'] != 100) {
            return PaymentPostDTO::fromData('failed');
        }
        return PaymentPostDTO::fromData('success', ['trackId' => $result['trackId']]);
    }

    public function verifyTransaction($callbackData, $price = null, Order $order = null): PaymentVerifyDTO
    {
        $trackId = $callbackData['trackId'] ?? null;
        if (!$trackId) {
            return PaymentVerifyDTO::fromData('failed');
        }
        $verifyData = [
            'merchant' => $this->merchant,
            'trackId'  => $trackId,
        ];
        $result = self::CallAPI(self::EP_VERIFY, $verifyData);
        if ($result && isset($result['result']) && ($result['result'] == 100 || $result['result'] == 201)) {
            return PaymentVerifyDTO::fromData('success', [
                'trackId'    => $result['trackId'] ?? null,
                'refNumber'  => $result['refNumber'] ?? null,
                'cardNumber' => $result['cardNumber'] ?? null,
            ]);
        }
        return PaymentVerifyDTO::fromData('failed');
    }

    public static function CallAPI($url, $data = false)
    {
        try {
            $request = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent'   => self::USER_AGENT,
            ])->withOptions(['verify' => false])->timeout(30);
            $response = $request->send('POST', $url, [
                'json' => $data ?: new \stdClass(),
            ]);
            $body = $response->body();
            return !empty($body) ? json_decode($body, true) : false;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function redirect(PaymentPostDTO $postData, Order $order): void
    {
        $trackId = $postData->getPostData()['trackId'];
        $url = self::EP_REDIRECT . $trackId;
        header('Location: ' . $url);
        exit;
    }
}
```

---

## ۳.۱۳ BankPaymentService (سرویس پرداخت)

```php
<?php
// Modules/Order/Services/BankPaymentService.php

namespace Rahweb\CmsCore\Modules\Order\Services;

use App\Library\SiteHelper;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Order\Entities\Bank;
use Rahweb\CmsCore\Modules\Order\Entities\Order;
use Rahweb\CmsCore\Modules\Order\Library\PaymentGatewayFactory;

class BankPaymentService
{
    public function handlePayment(Bank $bank, Order $order, int $price)
    {
        $site = SiteHelper::getInformation();
        $gateway = PaymentGatewayFactory::create($bank, $site['site_name']);
        $response = $gateway->getBankToken($price, $order);
        Auth::loginUsingId($order->user_id);

        if ($response->getStatus() === 'failed') {
            $order->delete();
            return redirect(route('basket.failed'))
                ->with('error', 'خطا در پرداخت، مجدد تلاش نمایید.');
        }

        $order->update([
            'transaction_info' => [
                'post' => $response->getPostData(),
                'verify' => []
            ]
        ]);
        $gateway->redirect($response, $order);
    }

    public function handleVerify(Order $order, array $callbackData, int $price): bool
    {
        $site = SiteHelper::getInformation();
        $gateway = PaymentGatewayFactory::create($order->bank, $site['site_name']);
        $response = $gateway->verifyTransaction($callbackData, $price, $order);
        Auth::loginUsingId($order->user_id);

        $transaction_info = json_decode($order->transaction_info, true);
        $transaction_info['verify'] = $response->getVerifyData() ?? [];
        $order->update([
            'transaction_info' => $transaction_info,
        ]);

        if ($response->getStatus() === 'failed') {
            $order->delete();
            return false;
        }
        return true;
    }
}
```

---

## ۳.۱۴ BankService (سرویس درگاه‌ها)

```php
<?php
// Modules/Order/Services/BankService.php

namespace Rahweb\CmsCore\Modules\Order\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Order\Entities\Bank;
use Rahweb\CmsCore\Modules\User\Entities\UserType;

class BankService
{
    public static function findAllActive()
    {
        $admin_test = false;
        if (Auth::check()) {
            $is_admin = UserType::where('user_id', Auth::user()->id)
                ->where('type', 'Admin')->first();
            if ($is_admin) {
                $admin_test = true;
            }
        }
        if ($admin_test) {
            return Bank::orderBy('id', 'DESC')->Active()->get();
        } else {
            return Bank::orderBy('id', 'DESC')->Active()->where('admin_test', 0)->get();
        }
    }

    public function update(int $id, $input)
    {
        $bank = Bank::findOrfail($id);
        $bank->update([
            'status' => $input['status'] ?? 1,
            'admin_test' => $input['admin_test'] ?? 0,
            'config' => json_encode(Arr::except($input, ['status', '_token']))
        ]);
    }
}
```

---

# ۴. ارسال و حمل‌ونقل

## ۴.۱ سرویس چاپار (ChaparShipment)

```php
<?php
// Modules/Order/Library/ChaparShipment.php

namespace Rahweb\CmsCore\Modules\Order\Library;

class ChaparShipment
{
    protected static string $baseUrl = 'http://api.krch.ir/v1/';
    protected static string $authToken = 'aW9zX2N1c3RvbWVyX2FwcDpUUFhAMjAxNg==';

    protected static function request(string $endpoint, array $postFields)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::$baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'APP-AUTH: ' . self::$authToken
            ],
        ]);
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new \Exception('Curl Error: ' . $error);
        }
        curl_close($curl);
        return json_decode($response, true);
    }

    /**
     * ثبت سفارش پستی به صورت bulk
     */
    public static function bulk(string $jsonInput)
    {
        $postFields = ['input' => $jsonInput];
        return self::request('bulk_import', $postFields);
    }

    /**
     * دریافت قیمت ارسال
     */
    public static function quote(array $orderData)
    {
        $inputJson = json_encode(['order' => $orderData], JSON_UNESCAPED_UNICODE);
        $postFields = ['input' => $inputJson];
        return self::request('get_quote', $postFields);
    }

    /**
     * دریافت لیست شعب چاپار
     */
    public static function agent()
    {
        return self::request('fetch_agent', []);
    }
}
```

---

## ۴.۲ نحوه محاسبه هزینه ارسال

هزینه ارسال در `BasketService::shipmentMethodPrice()` بر اساس **۳ نوع** محاسبه می‌شود:

### نوع ۱: قیمت ثابت (fixed_price)
```php
$price = $shippingMethod->price;
```

### نوع ۲: بر اساس وزن (with_weight)
```php
// به ازای هر ۱۰۰ گرم
$price = ($weight_sum / 100) * $shippingMethod->price;
// وزن به صورت گرد شده به بالا (هر ۱۰۰ گرم)
$weight_sum = ceil($weight_sum / 100) * 100;
```

### نوع ۳: چاپار (API)
```php
$response = ChaparShipment::quote([
    'origin' => $shippingMethod->sender_city->chapar_id,
    'destination' => $address->city->chapar_id,
    'weight' => $weight_sum / 1000, // وزن به کیلوگرم
    'value' => $final_price_sum . '0', // قیمت به ریال
    'method' => $shippingMethod->chapar_type,
]);
$price = ($response['objects']['order']['price']['fld_Total_Cost']) / 10; // ریال به تومان
```

### استثنائات:
- **پس کرایه** (`freight_balance == 1`): قیمت ارسال = ۰
- **سقف قیمت** (`price_ceiling != 0`): اگر مبلغ سفارش بیشتر از سقف باشد، ارسال رایگان

---

## ۴.۳ نحوه ثبت سفارش پستی (چاپار)

در `OrderController::changeShippingStatus()` وقتی وضعیت به "آماده ارسال" تغییر می‌کند:

```php
$bulkData = [
    'user' => [
        'username' => json_decode(@$shipping_method['config'], true)['user_name'],
        'password' => json_decode(@$shipping_method['config'], true)['password']
    ],
    'bulk' => [[
        'cn' => [
            'reference' => $data->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'assinged_pieces' => 0,
            'service' => $shipping_method->chapar_type,
            'value' => $data->total_price . '0',
            'payment_term' => $shipping_method->freight_balance,
            'weight' => $data->total_weight,
            'content' => '',
            'note' => '',
            'inv_value' => $data->total_price . '0'
        ],
        'sender' => [
            'person' => $shipping_method->sender_name,
            'company' => $shipping_method->sender_company,
            'city_no' => $shipping_method->sender_city->chapar_id,
            'telephone' => $shipping_method->sender_phone,
            'mobile' => $shipping_method->sender_mobile,
            'email' => $shipping_method->sender_email,
            'address' => $shipping_method->sender_address,
            'postcode' => $shipping_method->sender_postal_code
        ],
        'receiver' => [
            'person' => $data->receiptor_full_name,
            'company' => '',
            'city_no' => $data->city->chapar_id,
            'telephone' => '',
            'mobile' => json_decode(@$data->address, true)['receiptor_mobile'],
            'email' => '',
            'address' => json_decode(@$data->address, true)['state'] . ' ' .
                         json_decode(@$data->address, true)['city'] . ' ' .
                         json_decode(@$data->address, true)['address'],
            'postcode' => json_decode(@$data->address, true)['postal_code']
        ]
    ]]
];

$response = ChaparShipment::bulk(json_encode($bulkData));
$post_code = $response['objects']['result'][0]['tracking'];
$data->update(['post_code' => $post_code]);
```

---

## ۴.۴ مدل ShippingMethod (خلاصه فیلدها)

| فیلد | توضیح |
|------|-------|
| title | نام روش ارسال |
| price | قیمت (تومان) |
| price_ceiling | سقف قیمت برای ارسال رایگان |
| status | فعال/غیرفعال |
| type | fixed_price / with_weight / chapar |
| chapar_type | نوع سرویس چاپار (زمینی=1, هوایی=6, پست=11, چاپار پلاس=35, پاکت=97) |
| freight_balance | پس کرایه (1=بله) |
| config | JSON شامل user_name و password چاپار |
| description | توضیحات |
| sender_* | اطلاعات فرستنده (نام, شرکت, شهر, تلفن, موبایل, ایمیل, آدرس, کدپستی) |

---

## ۴.۵ چاپ لیبل پستی

```php
// OrderController::postLabel()
public function postLabel($id)
{
    $data = Order::findOrfail($id);
    $instagramMod = $this->socialService->getInstagram();
    if ($instagramMod) {
        $url = $instagramMod->link;
        $path = parse_url($url, PHP_URL_PATH);
        $instagram = trim($path, '/');
        return view('CmsCore::order.post-label.index', compact('data', 'instagram'));
    }
    return view('CmsCore::order.post-label.index', compact('data'));
}
```

> ویوی `CmsCore::order.post-label.index` شامل اطلاعات سفارش, آدرس فرستنده/گیرنده, کد پیگیری پستی, و QR/بارکد است.

---

## ۴.۶ SnappPayService (بررسی اقساط)

```php
<?php
// Modules/Order/Services/SnappPayService.php

namespace Rahweb\CmsCore\Modules\Order\Services;

use App\Library\SiteHelper;
use Rahweb\CmsCore\Modules\Order\Entities\Bank;
use Rahweb\CmsCore\Modules\Order\Library\SnappPay;

class SnappPayService
{
    public static function checkSnappay($price)
    {
        $site = SiteHelper::getInformation();
        $bank = Bank::orderBy('id', 'DESC')->where('bank_type', 'snappay')->first();
        $snapp = new SnappPay($bank, $site['site_name']);
        $snapp_data = [
            'snapp_show' => false,
            'snapp_title_message' => null,
            'snapp_description' => null,
        ];
        $response = $snapp->eligible($price);
        if ($response && @$response['response']['eligible'] == true) {
            $snapp_data = [
                'snapp_show' => true,
                'snapp_title_message' => $response['response']['title_message'],
                'snapp_description' => $response['response']['description'],
            ];
        }
        return $snapp_data;
    }
}
```
---

# ۵. پنل کاربری

## ۵.۱ Panel OrderController (لیست و جزئیات سفارشات)

```php
<?php
// app/Http/Controllers/Panel/OrderController.php

namespace App\Http\Controllers\Panel;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Order\Entities\Order;
use Rahweb\CmsCore\Modules\Order\Services\OrderService;
use Rahweb\CmsCore\Modules\Setting\Entities\Setting;
use Rahweb\CmsCore\Modules\Setting\Services\SettingService;

class OrderController extends Controller
{
    /**
     * لیست سفارشات کاربر
     */
    public function index()
    {
        $user = Auth::user();
        return view('pages.panel.order.list', compact('user'));
    }

    /**
     * جزئیات سفارش
     */
    public function detail($id)
    {
        $order = OrderService::findById($id);
        return view('pages.panel.order.detail', compact('order'));
    }

    /**
     * فاکتور قابل چاپ
     */
    public function factor($id)
    {
        $order = OrderService::findById($id);
        if (!$order) {
            abort(404, 'سفارش یافت نشد.');
        }
        $settings = SettingService::getFormatSettings([
            'siteName_fa', 'logo', 'main_phone_number', 'footer_logo'
        ]);
        return view('pages.panel.order.factor', compact('order', 'settings'));
    }
}
```

---

## ۵.۲ Panel AddressController (مدیریت آدرس‌ها)

```php
<?php
// app/Http/Controllers/Panel/AddressController.php

namespace App\Http\Controllers\Panel;

use App\Library\NumberHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Rahweb\CmsCore\Modules\Location\Entities\Address;
use Rahweb\CmsCore\Modules\Location\Entities\City;
use Rahweb\CmsCore\Modules\Location\Entities\State;
use Rahweb\CmsCore\Modules\Location\Http\Resources\AddressCollection;
use Rahweb\CmsCore\Modules\Location\Services\AddressService;
use Rahweb\CmsCore\Modules\Location\Services\CityService;
use Rahweb\CmsCore\Modules\Location\Services\StateService;

class AddressController extends Controller
{
    /**
     * صفحه اصلی مدیریت آدرس‌ها
     */
    public function index()
    {
        $user = Auth::user();
        return view('pages.panel.addresses.index', compact('user'));
    }

    /**
     * دریافت آدرس‌های کاربر (AJAX)
     */
    public function userAddresses()
    {
        $addresses = AddressService::find();
        $addressCollection = new AddressCollection($addresses['addresses']);
        return response()->json([
            'addresses' => @$addressCollection,
        ], 200);
    }

    /**
     * دریافت لیست استان‌ها
     */
    public function states()
    {
        $states = StateService::findAll();
        return response()->json(['states' => $states]);
    }

    /**
     * دریافت شهرها بر اساس استان
     */
    public function city(Request $request)
    {
        $req = $request->all();
        $cities = CityService::findOne($req['state_id']);
        return response()->json(['cities' => $cities]);
    }

    /**
     * ایجاد آدرس جدید
     */
    public function create(Request $request)
    {
        $user_id = @Auth::id();
        $state_id = @$request->get('state_id');
        $city_id = @$request->get('city_id');
        $address = @$request->get('address');
        $postal_code = \Rahweb\CmsCore\Modules\General\Helper\NumberHelper::persian2LatinDigit(
            @$request->get('postal_code')
        );
        $receiptor_full_name = @$request->get('receiptor_full_name');
        $receiptor_mobile = NumberHelper::persian2LatinDigit(@$request->get('receiptor_mobile'));
        AddressService::create(
            $user_id, $state_id, $city_id, $address,
            $postal_code, $receiptor_full_name, $receiptor_mobile
        );
        return redirect()->back()->with('success', 'آدرس با موفقیت اضافه شد');
    }

    /**
     * دریافت آدرس برای ویرایش
     */
    public function edit(Request $request)
    {
        $address = AddressService::findOne($request->get('address_id'));
        return response()->json(['address' => $address]);
    }

    /**
     * بروزرسانی آدرس
     */
    public function update(Request $request)
    {
        $user_id = @Auth::id();
        $address_id = @$request->get('address_id');
        $state_id = @$request->get('state_id');
        $city_id = @$request->get('city_id');
        $address = @$request->get('address');
        $postal_code = NumberHelper::persian2LatinDigit(@$request->get('postal_code'));
        $receiptor_full_name = @$request->get('receiptor_full_name');
        $receiptor_mobile = NumberHelper::persian2LatinDigit(@$request->get('receiptor_mobile'));
        AddressService::update(
            $address_id, $user_id, $state_id, $city_id, $address,
            $postal_code, $receiptor_full_name, $receiptor_mobile
        );
        return redirect()->back()->with('success', 'آدرس با موفقیت ویرایش شد');
    }
}
```

---

## ۵.۳ ویوهای پنل کاربری

### لیست آدرس‌ها
```
resources/views/pages/panel/addresses/index.blade.php
resources/views/pages/panel/addresses/_partials/add-modal.blade.php
resources/views/pages/panel/addresses/_partials/edit-form.blade.php
resources/views/pages/panel/addresses/_partials/form.blade.php
resources/views/pages/panel/addresses/_partials/inner-form.blade.php
resources/views/pages/panel/addresses/_partials/vue.blade.php
```

### لیست سفارشات
```
resources/views/pages/panel/order/list.blade.php     (لیست سفارشات)
resources/views/pages/panel/order/detail.blade.php    (جزئیات سفارش)
resources/views/pages/panel/order/factor.blade.php    (فاکتور قابل چاپ)
```

---

# ۶. پنل ادمین

## ۶.۱ OrderController (ادمین - لیست سفارشات با فیلتر)

```php
<?php
// Modules/Order/Http/Controllers/OrderController.php

namespace Rahweb\CmsCore\Modules\Order\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Rahweb\CmsCore\Modules\General\Helper\NumberHelper;
use Rahweb\CmsCore\Modules\General\Helper\Sms;
use Rahweb\CmsCore\Modules\Order\Entities\Bank;
use Rahweb\CmsCore\Modules\Order\Entities\Order;
use Rahweb\CmsCore\Modules\Order\Entities\OrderItem;
use Rahweb\CmsCore\Modules\Order\Entities\OrderShippingStatus;
use Rahweb\CmsCore\Modules\Order\Entities\ShippingMethod;
use Rahweb\CmsCore\Modules\Order\Filters\OrderFilter;
use Rahweb\CmsCore\Modules\Order\Library\ChaparShipment;
use Rahweb\CmsCore\Modules\Order\Services\OrderService;
use Rahweb\CmsCore\Modules\Order\Services\OrderShippingStatusService;
use Rahweb\CmsCore\Modules\Order\Services\ReturnService;
use Rahweb\CmsCore\Modules\Order\Services\ShippingMethodService;
use Rahweb\CmsCore\Modules\Order\Exports\OrderExport;
use Rahweb\CmsCore\Modules\Setting\Services\SettingService;
use Rahweb\CmsCore\Modules\Setting\Services\SocialService;

class OrderController extends Controller
{
    protected $shippingMethodService;
    protected $orderService;

    public function __construct(
        ShippingMethodService $shippingMethodService,
        ReturnService $returnService,
        OrderService $orderService,
        SocialService $socialService,
    ) {
        $this->shippingMethodService = $shippingMethodService;
        $this->orderService = $orderService;
        $this->returnService = $returnService;
        $this->socialService = $socialService;
    }

    /**
     * لیست سفارشات با فیلتر
     */
    public function index(Request $request)
    {
        $query = Order::query();
        $shipping_methods = ShippingMethod::orderBy('id', 'DESC')->get();
        $shipping_statuses = OrderShippingStatus::orderBy('id', 'DESC')->get();

        if ($request->has(['filter'])) {
            $filters = [
                'full_name' => $request->input('full_name'),
                'shipping_method_id' => $request->input('shipping_method_id'),
                'shipping_status_id' => $request->input('shipping_status_id'),
                'order_status' => $request->input('order_status'),
                'user_id' => $request->input('user_id'),
                'id' => $request->input('id'),
                'transactionId' => $request->input('transactionId'),
                'mobile' => $request->input('mobile'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
            ];
            $query = app(OrderFilter::class)->apply($query, $filters);
        }

        $orders = $query->orderBy('id', 'DESC')->paginate(20);
        return view('CmsCore::order.order.index', compact('orders', 'shipping_statuses', 'shipping_methods'));
    }

    /**
     * جزئیات سفارش
     */
    public function detail(int $id)
    {
        $data = Order::findOrfail($id);
        $shipping_statuses = OrderShippingStatus::orderBy('id', 'DESC')->get();
        return view('CmsCore::order.order.detail', compact('data', 'shipping_statuses'));
    }

    /**
     * فاکتور قابل چاپ
     */
    public function factor($id)
    {
        $order = Order::findOrfail($id);
        if ($order->order_status == "paying") {
            return redirect()->back()->with('success', 'امکان مشاهده نسخه قابل چاپ وجود ندارد');
        }
        $settings = SettingService::getFormatSettings([
            'siteName_fa', 'logo', 'main_phone_number', 'footer_logo'
        ]);
        return view('CmsCore::order.order.factor', compact('order', 'settings'));
    }

    /**
     * چاپ لیبل پستی
     */
    public function postLabel($id)
    {
        $data = Order::findOrfail($id);
        $instagramMod = $this->socialService->getInstagram();
        if ($instagramMod) {
            $url = $instagramMod->link;
            $path = parse_url($url, PHP_URL_PATH);
            $instagram = trim($path, '/');
            return view('CmsCore::order.post-label.index', compact('data', 'instagram'));
        }
        return view('CmsCore::order.post-label.index', compact('data'));
    }

    /**
     * تغییر وضعیت ارسال + ثبت سفارش چاپار + ارسال پیامک
     */
    public function changeShippingStatus(Request $request, $id)
    {
        $data = Order::findOrfail($id);
        if ($data['shipping_status_id'] != $request->get('shipping_status_id')) {
            $status = OrderShippingStatusService::findById($request->get('shipping_status_id'));
            $shipping_method = $data->shipping_method;
            $post_code = null;

            // اگر وضعیت "آماده ارسال" باشد → ثبت سفارش چاپار
            if ($status->for_send == 1) {
                $bulkData = [
                    'user' => [
                        'username' => json_decode(@$shipping_method['config'], true)['user_name'],
                        'password' => json_decode(@$shipping_method['config'], true)['password']
                    ],
                    'bulk' => [[
                        'cn' => [
                            'reference' => $data->id,
                            'date' => Carbon::now()->format('Y-m-d'),
                            'assinged_pieces' => 0,
                            'service' => $shipping_method->chapar_type,
                            'value' => $data->total_price . '0',
                            'payment_term' => $shipping_method->freight_balance,
                            'weight' => $data->total_weight,
                            'content' => '',
                            'note' => '',
                            'inv_value' => $data->total_price . '0'
                        ],
                        'sender' => [
                            'person' => $shipping_method->sender_name,
                            'company' => $shipping_method->sender_company,
                            'city_no' => $shipping_method->sender_city->chapar_id,
                            'telephone' => $shipping_method->sender_phone,
                            'mobile' => $shipping_method->sender_mobile,
                            'email' => $shipping_method->sender_email,
                            'address' => $shipping_method->sender_address,
                            'postcode' => $shipping_method->sender_postal_code
                        ],
                        'receiver' => [
                            'person' => $data->receiptor_full_name,
                            'company' => '',
                            'city_no' => $data->city->chapar_id,
                            'telephone' => '',
                            'mobile' => json_decode(@$data->address, true)
                                ? json_decode(@$data->address, true)['receiptor_mobile']
                                : $data->user->mobile,
                            'email' => '',
                            'address' => json_decode(@$data->address, true)
                                ? json_decode(@$data->address, true)['state'] . ' ' .
                                  json_decode(@$data->address, true)['city'] . ' ' .
                                  json_decode(@$data->address, true)['address']
                                : "",
                            'postcode' => json_decode(@$data->address, true)
                                ? json_decode(@$data->address, true)['postal_code']
                                : ""
                        ]
                    ]]
                ];

                $response = ChaparShipment::bulk(json_encode($bulkData));
                if (!empty(trim(@$response['objects']['result'][0]['tracking']))) {
                    $post_code = @$response['objects']['result'][0]['tracking'];
                }
                $data->update(['post_code' => @$post_code]);
            }

            $data->update([
                'shipping_status_id' => $request->get('shipping_status_id'),
            ]);

            // ارسال پیامک در صورت فعال بودن
            if (@$data->shipping_status->sending_sms == 1 && @$data->user->mobile != null) {
                $status_title = @$data->shipping_status->title;
                $kavenegar = new Sms();
                $kavenegar->sendLookup("userChangeStatus", [
                    "token" => "$data->id",
                    "token10" => "$status_title",
                ], $data->user->mobile);
            }
        }

        return redirect()->route('admin.order.index')->with('success', 'وضعیت ویرایش شد.');
    }

    /**
     * ثبت کد پیگیری پستی
     */
    public function postCode(Request $request)
    {
        $data = Order::findOrFail($request->get('order_id'));
        $data->update(['post_code' => @$request->get('post_code')]);

        if (@$data->user->mobile) {
            $kavenegar = new Sms();
            $kavenegar->sendLookup("sendPostCode", [
                "token" => "$data->id",
                "token10" => "$data->post_code",
            ], $data->user->mobile);
        }
        return redirect()->back()->with('success', 'کد پیگیری پستی با موفقیت ارسال شد.');
    }

    /**
     * تأیید فیش تصویری → وضعیت paid
     */
    public function imageAccept($id)
    {
        $order = Order::findOrfail($id);
        $order->update(['order_status' => "paid"]);
        return Redirect::back()->with('success', 'سفارش تأیید شد');
    }

    /**
     * رد فیش تصویری → وضعیت unpaid + برگشت موجودی
     */
    public function imageDecline($id)
    {
        $order = Order::findOrfail($id);
        $order->update(['order_status' => "unpaid"]);
        foreach ($order->items as $item) {
            $this->orderService->reverseStockAndPrice($item);
        }
        return Redirect::back()->with('success', 'سفارش رد شد');
    }

    /**
     * مرجوع کل سفارش
     */
    public function orderReturn($id)
    {
        $order = Order::findOrfail($id);
        foreach ($order->items as $item) {
            $this->returnService->returnWithCount($item, 0);
        }
        if ($order->bank->bank_type == "snappay") {
            $cancelation = $this->returnService->snappPayCancelation($order);
            if ($cancelation->getSuccessful() == "false") {
                $message = @$cancelation->getErrorData()['errorCode'] . ': ' .
                           @$cancelation->getErrorData()['message'];
                return Redirect::back()->with('error', $message);
            }
        }
        return Redirect::back()->with('success', 'آیتم موردنظر با موفقیت مرجوع شد');
    }

    /**
     * مرجوعی جزئی یک آیتم
     */
    public function return(Request $request, $id)
    {
        $item = OrderItem::findOrfail($id);
        $quantity = NumberHelper::persian2LatinDigit($request->get('quantity'));
        if ($item->quantity < $quantity) {
            return Redirect::back()->with('error', 'مقدار وارد شده بیشتر از تعداد محصول میباشد');
        }
        $order = $item->order;
        $this->returnService->returnWithCount($item, $quantity);
        if ($order->bank->bank_type == "snappay") {
            $cancelation = $this->returnService->snappPayUpdate($order);
            if ($cancelation->getSuccessful() == "false") {
                $message = @$cancelation->getErrorData()['errorCode'] . ': ' .
                           @$cancelation->getErrorData()['message'];
                return Redirect::back()->with('error', $message);
            }
        }
        return Redirect::back()->with('success', 'آیتم موردنظر با موفقیت مرجوع شد');
    }

    /**
     * خروجی اکسل سفارشات
     */
    public function export(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '360000');
        return Excel::download(new OrderExport($request), 'orders.xlsx');
    }
}
```

---

## ۶.۲ BasketController (ادمین - لیست سبدهای خرید)

```php
<?php
// Modules/Order/Http/Controllers/BasketController.php

namespace Rahweb\CmsCore\Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Rahweb\CmsCore\Modules\Location\Entities\State;
use Rahweb\CmsCore\Modules\Order\Entities\Basket;
use Rahweb\CmsCore\Modules\Order\Exports\BasketExport;
use Rahweb\CmsCore\Modules\Order\Services\BasketAdminService;

class BasketController extends Controller
{
    protected $BasketAdminService;

    public function __construct(BasketAdminService $BasketAdminService)
    {
        $this->BasketAdminService = $BasketAdminService;
    }

    /**
     * لیست سبدهای خرید با فیلتر
     */
    public function index(Request $request)
    {
        $baskets = $this->BasketAdminService->getList($request);
        $states = State::all();
        return view('CmsCore::order.basket.index', compact('baskets', 'states'));
    }

    /**
     * جزئیات سبد
     */
    public function detail($id)
    {
        $basket = Basket::findOrFail($id);
        $data = $this->BasketAdminService->getDetailData($basket);
        return view('CmsCore::order.basket.detail', compact('basket', 'data'));
    }

    public function getCities(Request $request)
    {
        return $this->BasketAdminService->getCities($request);
    }

    public function getUsers(Request $request)
    {
        return $this->BasketAdminService->getUsers($request);
    }

    public function getProducts(Request $request)
    {
        return $this->BasketAdminService->getProducts($request);
    }

    public function export(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '360000');
        return Excel::download(new BasketExport($request), 'baskets.xlsx');
    }
}
```

---

## ۶.۳ BankController (ادمین - مدیریت درگاه‌ها)

```php
<?php
// Modules/Order/Http/Controllers/BankController.php

namespace Rahweb\CmsCore\Modules\Order\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Rahweb\CmsCore\Modules\Order\Entities\Bank;
use Rahweb\CmsCore\Modules\Order\Services\BankService;

class BankController extends Controller
{
    protected $bankService;

    public function __construct(BankService $bankService)
    {
        $this->bankService = $bankService;
    }

    public function index()
    {
        $query = Bank::query();
        $banks = $query->orderby('id', 'DESC')->paginate(20);
        return view('CmsCore::order.bank.index', compact('banks'));
    }

    public function edit($id)
    {
        $data = Bank::findOrfail($id);
        $bank_fields = Config::get('order.my_banks')[$data->bank_type];
        return view('CmsCore::order.bank.edit', compact('data', 'bank_fields'));
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $input['status'] = $request->get('status') ? 1 : 0;
        $input['admin_test'] = $request->get('admin_test') ? 1 : 0;
        $this->bankService->update($id, $input);
        return redirect()->route('admin.bank.index', ['page' => $request->get('fallback_page', 1)])
            ->with('success', 'درگاه ویرایش شد.');
    }
}
```

---

## ۶.۴ DiscountController (ادمین - مدیریت تخفیف‌ها)

```php
<?php
// Modules/Order/Http/Controllers/DiscountController.php

namespace Rahweb\CmsCore\Modules\Order\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Rahweb\CmsCore\Modules\General\Helper\MakeTree;
use Rahweb\CmsCore\Modules\Order\DTO\DiscountDTO;
use Rahweb\CmsCore\Modules\Order\Entities\Discount;
use Rahweb\CmsCore\Modules\Order\Filters\DiscountFilter;
use Rahweb\CmsCore\Modules\Order\Http\Requests\DiscountRequest;
use Rahweb\CmsCore\Modules\Order\Services\DiscountService;
use Rahweb\CmsCore\Modules\Product\Entities\Brand;
use Rahweb\CmsCore\Modules\Product\Entities\ProductCategory;
use Rahweb\CmsCore\Modules\User\Entities\User;

class DiscountController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    public function index(Request $request)
    {
        $query = Discount::query();
        if ($request->has(['filter'])) {
            $filters = [
                'title' => $request->input('title'),
                'user_id' => $request->input('user_id'),
            ];
            $query = app(DiscountFilter::class)->apply($query, $filters);
        }
        $discounts = $query->orderByDesc('id')->paginate(20);
        return view('CmsCore::order.discount.index', compact('discounts'));
    }

    public function create()
    {
        $user = User::all();
        $product_categories = ProductCategory::orderBy('id', 'DESC')
            ->select(['title', 'id', 'parent_id'])->get();
        if (!empty($product_categories)) {
            MakeTree::getData($product_categories);
            $product_categories = MakeTree::GenerateArray(['get']);
        }
        $selected_categories = [];
        $brands = Brand::all();
        $selected_brands = [];
        return view('CmsCore::order.discount.create', compact(
            'user', 'product_categories', 'selected_categories', 'brands', 'selected_brands'
        ));
    }

    public function store(DiscountRequest $request)
    {
        $this->discountService->create(DiscountDTO::fromRequest($request));
        return redirect()->route('admin.discount.index', ['page' => $request->get('fallback_page', 1)])
            ->with('success', 'تخفیف جدید اضافه شد.');
    }

    public function edit(int $id)
    {
        $data = Discount::findOrfail($id);
        $user = User::all();
        $product_categories = ProductCategory::orderBy('id', 'DESC')
            ->select(['title', 'id', 'parent_id'])->get();
        if (!empty($product_categories)) {
            MakeTree::getData($product_categories);
            $product_categories = MakeTree::GenerateArray(['get']);
        }
        $selected_categories = $data->productCategories->pluck('id')->toArray();
        $brands = Brand::all();
        $selected_brands = $data->brands->pluck('id')->toArray();
        return view('CmsCore::order.discount.edit', compact(
            'data', 'user', 'product_categories', 'selected_categories', 'brands', 'selected_brands'
        ));
    }

    public function update(DiscountRequest $request, $id)
    {
        $this->discountService->update($id, DiscountDTO::fromRequest($request));
        return redirect()->route('admin.discount.index', ['page' => $request->get('fallback_page', 1)])
            ->with('success', 'تخفیف ویرایش شد.');
    }

    public function destroy($id)
    {
        $this->discountService->deleteOne($id);
        return Redirect::back()->with('success', 'آیتم موردنظر با موفقیت حذف شد');
    }
}
```

---

## ۶.۵ ShippingMethodController (ادمین - مدیریت روش‌های ارسال)

```php
<?php
// Modules/Order/Http/Controllers/ShippingMethodController.php

namespace Rahweb\CmsCore\Modules\Order\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Rahweb\CmsCore\Modules\Location\Entities\City;
use Rahweb\CmsCore\Modules\Location\Entities\State;
use Rahweb\CmsCore\Modules\Order\DTO\ShippingMethodDTO;
use Rahweb\CmsCore\Modules\Order\Entities\ShippingMethod;
use Rahweb\CmsCore\Modules\Order\Filters\ShippingMethodFilter;
use Rahweb\CmsCore\Modules\Order\Http\Requests\ShippingMethodRequest;
use Rahweb\CmsCore\Modules\Order\Services\ShippingMethodService;

class ShippingMethodController extends Controller
{
    protected $shippingMethodService;

    public function __construct(ShippingMethodService $shippingMethodService)
    {
        $this->shippingMethodService = $shippingMethodService;
    }

    public function index(Request $request)
    {
        $query = ShippingMethod::query();
        if ($request->has(['title'])) {
            $filters = ['title' => $request->input('title')];
            $query = app(ShippingMethodFilter::class)->apply($query, $filters);
        }
        $methods = $query->orderByDesc('id')->paginate(20);
        return view('CmsCore::order.shipping-method.index', compact('methods'));
    }

    public function create()
    {
        $cities = City::all()->map(function ($city) {
            return ['id' => $city->id, 'name' => $city->name, 'checked' => false];
        });
        $methods = Config::get('order.shipping_methods');
        return view('CmsCore::order.shipping-method.create', compact('cities', 'methods'));
    }

    public function store(ShippingMethodRequest $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '360000');
        $this->shippingMethodService->create(ShippingMethodDTO::fromRequest($request));
        return redirect()->route('admin.shipping-method.index', ['page' => $request->get('fallback_page', 1)])
            ->with('success', 'روش ارسال جدید اضافه شد.');
    }

    public function edit(int $id)
    {
        $data = ShippingMethod::findOrFail($id);
        $cities = City::all()->map(function ($city) use ($data) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'checked' => $data->cities->contains($city->id) ? true : false,
            ];
        });
        $methods = Config::get('order.shipping_methods');
        return view('CmsCore::order.shipping-method.edit', compact('data', 'cities', 'methods'));
    }

    public function update(ShippingMethodRequest $request, $id)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '360000');
        $this->shippingMethodService->update($id, ShippingMethodDTO::fromRequest($request));
        return redirect()->route('admin.shipping-method.index', ['page' => $request->get('fallback_page', 1)])
            ->with('success', 'روش ارسال ویرایش شد.');
    }

    public function destroy($id)
    {
        $this->shippingMethodService->deleteOne($id);
        return Redirect::back()->with('success', 'آیتم موردنظر با موفقیت حذف شد');
    }
}
```

---

## ۶.۶ OrderShippingStatusController (ادمین - وضعیت‌های ارسال)

```php
<?php
// Modules/Order/Http/Controllers/OrderShippingStatusController.php

namespace Rahweb\CmsCore\Modules\Order\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Rahweb\CmsCore\Modules\Order\DTO\OrderShippingStatusDTO;
use Rahweb\CmsCore\Modules\Order\Entities\OrderShippingStatus;
use Rahweb\CmsCore\Modules\Order\Filters\OrderShippingStatusFilter;
use Rahweb\CmsCore\Modules\Order\Http\Requests\OrderShippingStatusRequest;
use Rahweb\CmsCore\Modules\Order\Services\OrderShippingStatusService;

class OrderShippingStatusController extends Controller
{
    protected $orderShippingStatusService;

    public function __construct(OrderShippingStatusService $orderShippingStatusService)
    {
        $this->orderShippingStatusService = $orderShippingStatusService;
    }

    public function index(Request $request)
    {
        $query = OrderShippingStatus::query();
        if ($request->has(['title'])) {
            $filters = ['title' => $request->input('title')];
            $query = app(OrderShippingStatusFilter::class)->apply($query, $filters);
        }
        $statuses = $query->orderByDesc('id')->paginate(20);
        return view('CmsCore::order.order-shipping-status.index', compact('statuses'));
    }

    public function create()
    {
        return view('CmsCore::order.order-shipping-status.create');
    }

    public function store(OrderShippingStatusRequest $request)
    {
        $this->orderShippingStatusService->create(OrderShippingStatusDTO::fromRequest($request));
        return redirect()->route('admin.order-shipping-status.index', ['page' => $request->get('fallback_page', 1)])
            ->with('success', 'وضعیت جدید اضافه شد.');
    }

    public function edit(int $id)
    {
        $data = OrderShippingStatus::findOrfail($id);
        return view('CmsCore::order.order-shipping-status.edit', compact('data'));
    }

    public function update(OrderShippingStatusRequest $request, $id)
    {
        $this->orderShippingStatusService->update($id, OrderShippingStatusDTO::fromRequest($request));
        return redirect()->route('admin.order-shipping-status.index', ['page' => $request->get('fallback_page', 1)])
            ->with('success', 'وضعیت ویرایش شد.');
    }

    public function destroy($id)
    {
        try {
            $this->orderShippingStatusService->deleteOne($id);
            return Redirect::back()->with('success', 'آیتم موردنظر با موفقیت حذف شد');
        } catch (\Exception $err) {
            return Redirect::back()->with('error', $err->getMessage());
        }
    }
}
```

---

## ۶.۷ ویوهای ادمین

```
Packages/core-cms/resources/views/order/
├── bank/
│   ├── index.blade.php          (لیست درگاه‌ها)
│   ├── edit.blade.php           (ویرایش درگاه)
│   ├── form.blade.php           (فرم درگاه)
│   └── bank-modal.blade.php
├── basket/
│   ├── index.blade.php          (لیست سبدهای خرید)
│   ├── detail.blade.php         (جزئیات سبد)
│   ├── search.blade.php         (فرم جستجو)
│   └── excel.blade.php
├── discount/
│   ├── index.blade.php          (لیست تخفیف‌ها)
│   ├── create.blade.php         (ایجاد تخفیف)
│   ├── edit.blade.php           (ویرایش تخفیف)
│   └── form.blade.php           (فرم تخفیف)
├── order/
│   ├── index.blade.php          (لیست سفارشات + فیلتر)
│   ├── detail.blade.php         (جزئیات سفارش)
│   ├── factor.blade.php         (فاکتور قابل چاپ)
│   ├── search.blade.php         (فرم جستجو)
│   └── excel.blade.php
├── order-shipping-status/
│   ├── index.blade.php          (لیست وضعیت‌ها)
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── form.blade.php
├── shipping-method/
│   ├── index.blade.php          (لیست روش‌های ارسال)
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── form.blade.php
│   ├── branches.blade.php       (شعب چاپار)
│   └── chapar-modal.blade.php
└── post-label/
    └── index.blade.php          (لیبل پستی)
```

---

## ۶.۸ OrderObserver (تاریخچه خودکار سفارش)

```php
<?php
// Modules/Order/Observers/OrderObserver.php

namespace Rahweb\CmsCore\Modules\Order\Observers;

use Rahweb\CmsCore\Modules\Order\Entities\Basket;
use Rahweb\CmsCore\Modules\Order\Entities\Order;
use Rahweb\CmsCore\Modules\Order\Entities\OrderHistory;

class OrderObserver
{
    public function created(Order $Order)
    {
        if ($Order->isDirty('order_status') || $Order->isDirty('shipping_status_id')) {
            OrderHistory::create([
                'order_id' => $Order->id,
                'order_status' => $Order['order_status'],
                'shipping_status_id' => $Order['shipping_status_id'],
            ]);
        }
    }

    public function updated(Order $Order)
    {
        if ($Order->isDirty('order_status') || $Order->isDirty('shipping_status_id')) {
            OrderHistory::create([
                'order_id' => $Order->id,
                'order_status' => $Order['order_status'],
                'shipping_status_id' => $Order['shipping_status_id'],
            ]);
            // حذف سبد خرید پس از پرداخت موفق
            if ($Order['order_status'] == "paid") {
                $basket = Basket::find($Order['basket_id']);
                if ($basket) {
                    $basket->delete();
                }
            }
        }
    }
}
```

---

## ۶.۹ ReturnService (مرجوعی)

```php
<?php
// Modules/Order/Services/ReturnService.php

namespace Rahweb\CmsCore\Modules\Order\Services;

use App\Library\SiteHelper;
use Rahweb\CmsCore\Modules\Order\Entities\Bank;
use Rahweb\CmsCore\Modules\Order\Library\SnappPay;
use Rahweb\CmsCore\Modules\Product\Entities\Product;
use Rahweb\CmsCore\Modules\Product\Entities\ProductVariant;

class ReturnService
{
    /**
     * مرجوعی آیتم با بازگشت موجودی
     */
    public function returnWithCount($item, $quantity)
    {
        $returned = intval($item->quantity) - intval($quantity);
        $item->update([
            'quantity' => intval($quantity),
            'old_quantity' => intval($item->quantity)
        ]);
        self::reverseStockAndPrice($item, $returned);
    }

    /**
     * بازگشت موجودی و بروزرسانی قیمت
     */
    public static function reverseStockAndPrice($item, $quantity)
    {
        $product = Product::find(@$item->product_id);
        if ($item->product_variant_id != null) {
            $product_variant_check = ProductVariant::find($item->product_variant_id);
            $product_variant_check->update([
                'stock' => intval(@$product_variant_check->stock) + intval($quantity),
            ]);
            $product_variant_check->save();
            $sum_stock = $product->variants()->orderBy('final_price', 'ASC')->sum('stock');
            $minimum_price_variant = $product->variants()
                ->where('price_affective', '1')
                ->orderBy('final_price', 'ASC')
                ->where('stock', '<>', '0')
                ->first();
            if ($minimum_price_variant == null) {
                $minimum_price_variant = $product->variants()
                    ->where('price_affective', '1')
                    ->orderBy('final_price', 'ASC')
                    ->first();
            }
            $product->update([
                'price' => $minimum_price_variant['price'],
                'discounted_price' => $minimum_price_variant['discounted_price'],
                'final_price' => $minimum_price_variant['final_price'],
                'stock' => $sum_stock,
            ]);
        } else {
            $product->update([
                'stock' => intval(@$product->stock) + intval($quantity),
            ]);
        }
        $product->save();
    }

    /**
     * لغو سفارش اسنپ‌پی
     */
    public function snappPayCancelation($order)
    {
        $site = SiteHelper::getInformation();
        $bank = Bank::findOrfail($order['bank_id']);
        $cancelation = new SnappPay($bank, $site['site_name']);
        return $cancelation->cancel($order);
    }

    /**
     * بروزرسانی سفارش اسنپ‌پی
     */
    public function snappPayUpdate($order)
    {
        $site = SiteHelper::getInformation();
        $bank = Bank::findOrfail($order['bank_id']);
        $cancelation = new SnappPay($bank, $site['site_name']);
        return $cancelation->update($order);
    }
}
```
---

# ۷. روت‌ها

## ۷.۱ روت‌های ادمین (Admin Routes)

```php
<?php
// Modules/Order/Routes/web.php

use Rahweb\CmsCore\Modules\Order\Http\Controllers\BankController;
use Rahweb\CmsCore\Modules\Order\Http\Controllers\BasketController;
use Rahweb\CmsCore\Modules\Order\Http\Controllers\DiscountController;
use Rahweb\CmsCore\Modules\Order\Http\Controllers\OrderController;
use Rahweb\CmsCore\Modules\Order\Http\Controllers\OrderShippingStatusController;
use Rahweb\CmsCore\Modules\Order\Http\Controllers\ShippingMethodController;

Route::middleware('AdminPermission')->group(function () {

    // ========== مدیریت درگاه‌های بانکی ==========
    Route::controller(BankController::class)
        ->prefix('admin/bank')
        ->name('admin.bank.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/edit/{id}', 'update')->name('edit');
        });

    // ========== مدیریت وضعیت‌های ارسال ==========
    Route::controller(OrderShippingStatusController::class)
        ->prefix('admin/order-shipping-status')
        ->name('admin.order-shipping-status.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/create', 'store')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/edit/{id}', 'update')->name('edit');
            Route::get('/delete/{id}', 'destroy')->name('delete');
        });

    // ========== مدیریت روش‌های ارسال ==========
    Route::controller(ShippingMethodController::class)
        ->prefix('admin/shipping-method')
        ->name('admin.shipping-method.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/create', 'store')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/edit/{id}', 'update')->name('edit');
            Route::get('/delete/{id}', 'destroy')->name('delete');
            Route::get('/chapar-branches', 'chaparBranch')
                ->name('chapar-branch')->withoutMiddleware('AdminPermission');
            Route::get('/sql-to-json', 'sqlToJson')
                ->name('sql-to-json')->withoutMiddleware('AdminPermission');
        });

    // ========== مدیریت تخفیف‌ها ==========
    Route::controller(DiscountController::class)
        ->prefix('admin/discount')
        ->name('admin.discount.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/create', 'store')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/edit/{id}', 'update')->name('edit');
            Route::get('/delete/{id}', 'destroy')->name('delete');
        });

    // ========== مدیریت سبدهای خرید ==========
    Route::controller(BasketController::class)
        ->prefix('admin/basket')
        ->name('admin.basket.')
        ->group(function () {
            Route::get('/get-users', 'getUsers')->name('index');
            Route::get('/get-cities', 'getCities')->name('index');
            Route::get('/get-products', 'getProducts')->name('index');
            Route::get('/', 'index')->name('index');
            Route::get('/detail/{id}', 'detail')->name('detail');
            Route::get('/export', 'export')
                ->name('export')->withoutMiddleware('AdminPermission');
        });

    // ========== مدیریت سفارشات ==========
    Route::controller(OrderController::class)
        ->prefix('admin/order')
        ->name('admin.order.')
        ->group(function () {
            Route::get('/convert-dates', 'convertDates')
                ->name('convert-dates')->withoutMiddleware('AdminPermission');
            Route::get('/', 'index')->name('index');
            Route::post('/change-shipping-status/{id}', 'changeShippingStatus')
                ->name('change-shipping-status');
            Route::post('/post-code/', 'postCode')
                ->name('post-code')->withoutMiddleware('AdminPermission');
            Route::get('/detail/{id}', 'detail')->name('detail');
            Route::get('/delete/{id}', 'destroy')->name('delete');
            Route::get('/factor/{id}', 'factor')->name('factor');
            Route::get('/post-label/{id}', 'postLabel')
                ->name('post-label')->withoutMiddleware('AdminPermission');
            Route::post('/return/{id}', 'return')->name('return');
            Route::get('/order-return/{id}', 'orderReturn')->name('order-return');
            Route::get('/image-accept/{id}', 'imageAccept')
                ->name('image-accept')->withoutMiddleware('AdminPermission');
            Route::get('/image-decline/{id}', 'imageDecline')
                ->name('image-decline')->withoutMiddleware('AdminPermission');
            Route::get('/export', 'export')
                ->name('export')->withoutMiddleware('AdminPermission');
        });
});
```

---

## ۷.۲ روت‌های سایت (Site Routes) - Checkout و Orders

```php
<?php
// routes/web.php (فقط بخش مرتبط با فروشگاه)

use App\Http\Controllers\Shop\AddressController;
use App\Http\Controllers\Shop\BasketController;
use App\Http\Controllers\Shop\OrderController as OrderControllerAlias;
use App\Http\Controllers\Panel\AddressController as AddressControllerAlias;
use App\Http\Controllers\Panel\OrderController;

// ==================== سبد خرید (بدون لاگین) ====================
Route::controller(BasketController::class)
    ->prefix('/checkout')
    ->name('basket.')
    ->group(function () {
        Route::post('/add-to-basket', 'add')->name('add');
        Route::get('/cart', 'cart')->name('cart');
        Route::get('/cart-delete', 'delete')->name('cart-delete');
        Route::post('/cart-item-remove', 'removeItem')
            ->name('cart-item-remove'); // vue
        Route::get('/cart-item-list', 'cartItems')
            ->name('cart-items'); // vue
        Route::get('/list-price', 'listPrice')
            ->name('list-price'); // vue
        Route::post('/menu-basket-items/', 'itemCount')
            ->name('count'); // vue
    });

// ==================== Checkout (نیاز به لاگین) ====================
Route::middleware('panel')
    ->prefix('/checkout')
    ->name('basket.')
    ->group(function () {

        // --- مرحله ۲: انتخاب آدرس و روش ارسال ---
        Route::controller(AddressController::class)
            ->group(function () {
                Route::get('/shipping', 'list')->name('shipping');
                Route::post('/address-create', 'create')->name('address-create');
                Route::post('/address-update', 'update')->name('address-update');
                Route::get('/address-list', 'userAddresses')
                    ->name('address-list'); // vue
                Route::post('/city', 'city')
                    ->name('cities'); // vue
                Route::get('/states', 'states')
                    ->name('states'); // vue
                Route::post('/address-edit', 'edit')
                    ->name('address-edit'); // vue
                Route::post('/shipments', 'shipments')
                    ->name('shipments'); // vue
                Route::post('/set-shipments', 'setShipments')
                    ->name('set-shipments'); // vue
                Route::get('/address-price', 'addressPrice')
                    ->name('address-price'); // vue
            });

        // --- مرحله ۳: پرداخت ---
        Route::controller(OrderControllerAlias::class)
            ->group(function () {
                Route::get('/payment', 'cart')->name('payment');
                Route::post('/add-discount', 'addDiscount')
                    ->name('add-discount'); // vue
                Route::get('/delete-discount', 'deleteDiscount')
                    ->name('delete-discount'); // vue
                Route::get('/order-price', 'orderPrice')
                    ->name('order-price'); // vue
                // ایجاد سفارش
                Route::post('/create', 'create')->name('order-create');
                // Callback درگاه‌های پرداخت (بدون middleware panel)
                Route::any('/finish', 'finishZarinPal')
                    ->name('finish.zarin-pal')->withoutMiddleware('panel');
                Route::any('/finish-saman', 'finishSaman')
                    ->name('finish.saman-bank')->withoutMiddleware('panel');
                Route::any('/finish-sadad', 'finishSadad')
                    ->name('finish.sadad-bank')->withoutMiddleware('panel');
                Route::any('/finish-snapp-pay', 'finishSnappPay')
                    ->name('finish.snapp-pay')->withoutMiddleware('panel');
                Route::any('/finish-saderat', 'finishSaderat')
                    ->name('finish.saderat')->withoutMiddleware('panel');
                Route::any('/finish-irandargah', 'finishIrDargah')
                    ->name('finish.irandargah')->withoutMiddleware('panel');
                Route::any('/finish-parsian', 'finishParsian')
                    ->name('finish.parsian')->withoutMiddleware('panel');
                Route::any('/finish-zibal', 'finishZibal')
                    ->name('finish.zibal-bank')->withoutMiddleware('panel');
                // صفحات نتیجه
                Route::get('/order-details-success/{id}', 'success')->name('success');
                Route::get('/order-details-deposit/{id}', 'successDeposit')
                    ->name('order-images');
                Route::get('/order-details-failed/', 'failed')->name('failed');
            });
    });

// ==================== پنل کاربری (نیاز به لاگین) ====================
Route::middleware('panel')
    ->prefix('/panel')
    ->name('panel.')
    ->group(function () {

        // سفارشات
        Route::controller(OrderController::class)
            ->group(function () {
                Route::get('/orders', 'index')->name('orders');
                Route::get('/order/{id}', 'detail')->name('order-detail');
                Route::get('/order-factor/{id}', 'factor')->name('order-factor');
            });

        // آدرس‌ها
        Route::controller(AddressControllerAlias::class)
            ->group(function () {
                Route::get('/address', 'index')->name('address');
                Route::post('/address-create', 'create')->name('address-create');
                Route::post('/address-update', 'update')->name('address-update');
                Route::get('/address-list', 'userAddresses')
                    ->name('address-list'); // vue
                Route::post('/city', 'city')
                    ->name('cities'); // vue
                Route::get('/states', 'states')
                    ->name('states'); // vue
                Route::post('/address-edit', 'edit')
                    ->name('address-edit'); // vue
            });
    });
```

---

## ۷.۳ روت‌های Location (ادمین)

```php
<?php
// Modules/Location/Routes/web.php

use Rahweb\CmsCore\Modules\Location\Http\Controllers\LocationController;

Route::middleware('AdminPermission')->group(function () {

    // مدیریت استان‌ها
    Route::controller(LocationController::class)
        ->prefix('admin/state')
        ->name('admin.state.')
        ->group(function () {
            Route::get('/', 'getState')->name('index');
            Route::get('/change-status/{id}', 'changeStateStatus')->name('change-status');
        });

    // مدیریت شهرها
    Route::controller(LocationController::class)
        ->prefix('admin/city')
        ->name('admin.city.')
        ->group(function () {
            Route::get('/', 'getCity')->name('index');
            Route::get('/change-status/{id}', 'changeCityStatus')->name('change-status');
        });

    // مدیریت آدرس‌ها
    Route::controller(LocationController::class)
        ->prefix('admin/address')
        ->name('admin.address.')
        ->group(function () {
            Route::get('/', 'getAddress')->name('index');
        });
});
```

---

## ۷.۴ خلاصه نام روت‌ها

### روت‌های سایت (Site)

| روت | متد | توضیح |
|-----|-----|-------|
| basket.add | POST | افزودن به سبد |
| basket.cart | GET | صفحه سبد خرید |
| basket.cart-delete | GET | حذف کل سبد |
| basket.cart-item-remove | POST | حذف آیتم |
| basket.shipping | GET | صفحه انتخاب آدرس |
| basket.shipments | POST | دریافت روش‌های ارسال |
| basket.set-shipments | POST | انتخاب روش ارسال |
| basket.address-price | GET | محاسبه قیمت نهایی |
| basket.payment | GET | صفحه پرداخت |
| basket.add-discount | POST | اعمال کد تخفیف |
| basket.order-price | GET | قیمت نهایی |
| basket.order-create | POST | ایجاد سفارش |
| basket.finish.zarin-pal | ANY | callback زرین‌پال |
| basket.finish.saman-bank | ANY | callback سامان |
| basket.finish.sadad-bank | ANY | callback سداد |
| basket.finish.snapp-pay | ANY | callback اسنپ‌پی |
| basket.finish.saderat | ANY | callback سپهر |
| basket.finish.irandargah | ANY | callback ایران درگاه |
| basket.finish.parsian | ANY | callback پارسیان |
| basket.finish.zibal-bank | ANY | callback زیبال |
| basket.success | GET | صفحه موفقیت |
| basket.failed | GET | صفحه شکست |
| panel.orders | GET | لیست سفارشات |
| panel.order-detail | GET | جزئیات سفارش |
| panel.address | GET | مدیریت آدرس‌ها |

### روت‌های ادمین

| روت | متد | توضیح |
|-----|-----|-------|
| admin.order.index | GET | لیست سفارشات |
| admin.order.detail | GET | جزئیات سفارش |
| admin.order.change-shipping-status | POST | تغییر وضعیت ارسال |
| admin.order.post-code | POST | ثبت کد پستی |
| admin.order.factor | GET | فاکتور |
| admin.order.post-label | GET | لیبل پستی |
| admin.order.return | POST | مرجوعی آیتم |
| admin.order.order-return | GET | مرجوعی کل سفارش |
| admin.order.image-accept | GET | تأیید فیش |
| admin.order.image-decline | GET | رد فیش |
| admin.order.export | GET | خروجی اکسل |
| admin.basket.index | GET | لیست سبدها |
| admin.bank.index | GET | لیست درگاه‌ها |
| admin.discount.index | GET | لیست تخفیف‌ها |
| admin.shipping-method.index | GET | لیست روش‌های ارسال |
| admin.order-shipping-status.index | GET | لیست وضعیت‌ها |

---

# ۸. سرویس‌ها و کلاس‌های کمکی

## ۸.۱ OrderService (سرویس اصلی سفارشات)

```php
<?php
// Modules/Order/Services/OrderService.php

namespace Rahweb\CmsCore\Modules\Order\Services;

use App\Library\SiteHelper;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\General\Helper\Sms;
use Rahweb\CmsCore\Modules\Order\Entities\Bank;
use Rahweb\CmsCore\Modules\Order\Entities\InventoryTransaction;
use Rahweb\CmsCore\Modules\Order\Entities\Order;
use Rahweb\CmsCore\Modules\Order\Entities\OrderItem;
use Rahweb\CmsCore\Modules\Order\Entities\OrderShippingStatus;
use Rahweb\CmsCore\Modules\Product\Entities\Product;
use Rahweb\CmsCore\Modules\Product\Entities\ProductVariant;
use Rahweb\CmsCore\Modules\Setting\Entities\Setting;

class OrderService
{
    protected $bankPaymentService;

    public function __construct(BankPaymentService $bankPaymentService)
    {
        $this->bankPaymentService = $bankPaymentService;
    }

    // ==================== جستجو ====================

    public function findByField($field, $value)
    {
        return Order::orderBy('id', 'DESC')->where($field, $value)->first();
    }

    public static function findByAuthority($authority)
    {
        return Order::orderBy('id', 'DESC')
            ->where('transaction_info->post->Authority', $authority)->first();
    }

    public static function findByToken($token)
    {
        return Order::orderBy('id', 'DESC')
            ->where('transaction_info->post->Token', $token)->first();
    }

    public static function findById($id)
    {
        return Order::findOrFail($id);
    }

    public static function byId($id)
    {
        return Order::find($id);
    }

    // ==================== ایجاد سفارش ====================

    public function create($basket, $user_description = null)
    {
        $currentOrder = Order::orderBy('id', 'DESC')
            ->where('basket_id', $basket['id'])->first();
        if ($currentOrder != null) {
            $currentOrder->delete();
        }
        return $this->createOrder($basket, $user_description);
    }

    private function createOrder($currentBasket, $user_description = null)
    {
        $default_shiping_status = OrderShippingStatus::orderBy('default', 'DESC')->first();
        $price_details = $this->priceCalculate();

        $order = Order::create([
            'user_id' => $currentBasket['user_id'],
            'basket_id' => $currentBasket['id'],
            'address_id' => @$currentBasket->address->id,
            'city_id' => @$currentBasket->address->city_id,
            'state_id' => @$currentBasket->address->state_id,
            'address' => json_encode([
                'state' => @$currentBasket->address->state->name,
                'city' => @$currentBasket->address->city->name,
                'address' => @$currentBasket->address->address,
                'receiptor_mobile' => @$currentBasket->address->receiptor_mobile,
                'postal_code' => @$currentBasket->address->postal_code,
            ]),
            'receiptor_full_name' => @$currentBasket->address->receiptor_full_name,
            'shipping_method_id' => @$currentBasket['shipping_method_id'],
            'bank_id' => @$currentBasket['bank_id'],
            'order_status' => "paying",
            'discount_id' => @$currentBasket['discount_id'],
            'shipping_price' => $price_details['shipping_price'],
            'total_price' => $price_details['total_price'],
            'discount_price' => $price_details['discount_price'],
            'payment_price' => $price_details['payment_price'],
            'deposit_price' => $price_details['deposit_price'],
            'remaining_price' => $price_details['remaining_price'],
            'current_tax' => $price_details['current_tax'],
            'tax_price' => $price_details['tax_price'],
            'freight_balance' => $price_details['freight_balance'],
            'user_description' => $user_description,
            'shipping_status_id' => $default_shiping_status ? $default_shiping_status->id : null,
        ]);
        $order->save();

        $weight_sum = 0;
        foreach ($currentBasket->items as $item) {
            $weight = $item->product_variant_id
                ? $item->productVariant->weight
                : $item->product->weight;
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => @$item->product_id,
                'product_variant_id' => @$item->product_variant_id,
                'quantity' => @$item->quantity,
                'weight' => @$weight,
                'price' => $item->product_variant_id
                    ? $item->productVariant->price
                    : $item->product->price,
                'discounted_price' => $item->product_variant_id
                    ? (intval($item->productVariant->discounted_price) != 0 ? $item->productVariant->discounted_price : 0)
                    : (intval($item->product->discounted_price) != 0 ? $item->product->discounted_price : 0),
            ]);
            $weight_quantity = $weight * $item->quantity;
            $weight_sum += $weight_quantity;
            if ($currentBasket->shippingMethod->type == "custom") {
                $weight_sum = ceil($weight_sum / 100) * 100;
            }
        }
        $order->total_weight = $weight_sum;
        $order->save();
        return $order;
    }

    // ==================== محاسبه قیمت ====================

    private function priceCalculate()
    {
        $deposit_price_val = intval(Setting::where('key', 'deposit_price')->first()['value']);
        $cart_deposit = intval(Setting::where('key', 'cart_deposit')->first()['value']);

        $shipping_price = DiscountService::listPrice()['price_shipping'];
        $total_price = DiscountService::listPrice()['final_price_sum'];
        $discount_price = DiscountService::listPrice()['discount_amount'];
        $payment_price = DiscountService::listPrice()['price_cart'];
        $deposit_price = ($cart_deposit != 0 && $payment_price >= $cart_deposit)
            ? ($deposit_price_val ? intval($deposit_price_val) : 0)
            : 0;
        $remaining_price = $deposit_price != 0 ? $payment_price - $deposit_price : 0;
        $current_tax = DiscountService::listPrice()['tax'];
        $tax_price = DiscountService::listPrice()['tax_value'];
        $freight_balance = DiscountService::listPrice()['freight_balance'];

        return [
            'shipping_price' => $shipping_price,
            'total_price' => $total_price,
            'discount_price' => $discount_price,
            'payment_price' => $payment_price,
            'tax_price' => $tax_price,
            'current_tax' => $current_tax,
            'freight_balance' => $freight_balance,
            'deposit_price' => $deposit_price,
            'remaining_price' => $remaining_price,
        ];
    }

    // ==================== checkout ====================

    public function checkout($currentOrder)
    {
        $price = intval(str_replace(',', '', $currentOrder->deposit_price)) != 0
            ? intval(str_replace(',', '', $currentOrder->deposit_price))
            : intval(str_replace(',', '', $currentOrder->payment_price));

        if ($currentOrder['discount_id'] != null && $price <= 0) {
            return $this->handleFreeOrder($currentOrder);
        }

        $bank = Bank::findOrfail($currentOrder['bank_id']);
        return $this->bankPaymentService->handlePayment($bank, $currentOrder, $price);
    }

    private function handleFreeOrder($currentOrder)
    {
        $currentOrder->update(['order_status' => "paid"]);
        self::inventory($currentOrder);

        if (Auth::user()->mobile) {
            $kavenegar = new Sms();
            $kavenegar->sendLookup("userBuy", ["token" => "$currentOrder->id"], Auth::user()->mobile);
        }
        $admin_mobile = Setting::where('key', 'admin_mobile')->whereNotNull('value')->first();
        if ($admin_mobile) {
            $kavenegar = new Sms();
            $kavenegar->sendLookup("adminBuy", ["token" => "$currentOrder->id"], $admin_mobile->value);
        }

        if (intval(str_replace(',', '', $currentOrder->deposit_price)) != 0) {
            return redirect(route('basket.order-images', ['id' => $currentOrder->id]));
        } else {
            return redirect(route('basket.success', ['id' => $currentOrder->id]));
        }
    }

    // ==================== مدیریت موجودی ====================

    public static function inventory($currentOrder)
    {
        foreach ($currentOrder->items as $item) {
            $resids = [
                'product_id' => @$item->product_id,
                'product_variant_id' => @$item->product_variant_id,
                'quantity' => @$item->quantity,
                'order_id' => $item->order_id,
                'type' => "add",
            ];
            self::setStockAndPrice($item);
        }
        InventoryTransaction::insert($resids);
    }

    public static function setStockAndPrice($item)
    {
        $product = Product::find(@$item->product_id);
        if ($item->product_variant_id != null) {
            $product_variant_check = ProductVariant::find($item->product_variant_id);
            $product_variant_check->update([
                'stock' => intval(@$product_variant_check->stock) - intval(@$item->quantity),
            ]);
            $product_variant_check->save();
            $sum_stock = $product->variants()->orderBy('final_price', 'ASC')->sum('stock');
            $minimum_price_variant = $product->variants()
                ->orderBy('final_price', 'ASC')
                ->where('stock', '<>', '0')->first();
            if ($minimum_price_variant == null) {
                $minimum_price_variant = $product->variants()
                    ->orderBy('final_price', 'ASC')->first();
            }
            $product->update([
                'price' => $minimum_price_variant['price'],
                'discounted_price' => $minimum_price_variant['discounted_price'],
                'final_price' => $minimum_price_variant['final_price'],
                'stock' => $sum_stock,
            ]);
        } else {
            $product->update([
                'stock' => intval(@$product->stock) - intval(@$item->quantity),
            ]);
        }
        $product->save();
    }

    public static function reverseStockAndPrice($item)
    {
        $product = Product::find(@$item->product_id);
        if ($item->product_variant_id != null) {
            $product_variant_check = ProductVariant::find($item->product_variant_id);
            $product_variant_check->update([
                'stock' => intval(@$product_variant_check->stock) + intval(@$item->quantity),
            ]);
            $product_variant_check->save();
            $sum_stock = $product->variants()->orderBy('final_price', 'ASC')->sum('stock');
            $minimum_price_variant = $product->variants()
                ->where('price_affective', '1')
                ->orderBy('final_price', 'ASC')
                ->where('stock', '<>', '0')->first();
            if ($minimum_price_variant == null) {
                $minimum_price_variant = $product->variants()
                    ->where('price_affective', '1')
                    ->orderBy('final_price', 'ASC')->first();
            }
            $product->update([
                'price' => $minimum_price_variant['price'],
                'discounted_price' => $minimum_price_variant['discounted_price'],
                'final_price' => $minimum_price_variant['final_price'],
                'stock' => $sum_stock,
            ]);
        } else {
            $product->update([
                'stock' => intval(@$product->stock) + intval(@$item->quantity),
            ]);
        }
        $product->save();
    }

    // ==================== Callback پرداخت ====================

    public function callback(string $field, string $value, array $data)
    {
        $order = $this->findByField($field, $value);
        if ($order == null) {
            return redirect(route('basket.cart'))->with('error', 'فاکتور معتبر نمی باشد');
        }
        if ($order->order_status == "deposit_paid" || $order->order_status == "paid") {
            if (intval(str_replace(',', '', $order->deposit_price)) != 0) {
                return redirect(route('basket.order-images', ['id' => $order->id]));
            } else {
                return redirect(route('basket.success', ['id' => $order->id]));
            }
        }
        $price = intval(str_replace(',', '', $order->deposit_price)) != 0
            ? intval(str_replace(',', '', $order->deposit_price))
            : intval(str_replace(',', '', $order->payment_price));

        $is_success_payment = $this->bankPaymentService->handleVerify($order, $data, $price);
        if ($is_success_payment) {
            return $this->handleSuccessOrderPayment($order);
        } else {
            return $this->handleFailedOrder();
        }
    }

    private function handleFailedOrder()
    {
        return redirect(route('basket.failed'))->with('error', 'خطا در پرداخت، مجدد تلاش نمایید.');
    }

    public function handleSuccessOrderPayment($order)
    {
        if ($order->order_status === "paid") {
            return redirect(route('basket.success', ['id' => $order->id]));
        }
        $order->update([
            'order_status' => intval($order->deposit_price) != 0 ? "deposit_paid" : "paid"
        ]);
        self::inventory($order);

        if (Auth::user()->mobile) {
            $kavenegar = new Sms();
            $kavenegar->sendLookup("userBuy", ["token" => "$order->id"], Auth::user()->mobile);
        }
        $admin_mobile = Setting::where('key', 'admin_mobile')->whereNotNull('value')->first();
        if ($admin_mobile) {
            $kavenegar = new Sms();
            $kavenegar->sendLookup("adminBuy", ["token" => "$order->id"], $admin_mobile->value);
        }

        if (intval(str_replace(',', '', $order->deposit_price)) != 0) {
            return redirect(route('basket.order-images', ['id' => $order->id]));
        } else {
            return redirect(route('basket.success', ['id' => $order->id]));
        }
    }
}
```

---

## ۸.۲ DiscountService (سرویس تخفیف و محاسبه قیمت)

```php
<?php
// Modules/Order/Services/DiscountService.php (فقط بخش‌های کلیدی)

namespace Rahweb\CmsCore\Modules\Order\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Order\DTO\DiscountDTO;
use Rahweb\CmsCore\Modules\Order\Entities\Basket;
use Rahweb\CmsCore\Modules\Order\Entities\Discount;
use Rahweb\CmsCore\Modules\Order\Entities\OrderShippingStatus;
use Rahweb\CmsCore\Modules\Order\Library\ChaparShipment;
use Rahweb\CmsCore\Modules\Setting\Entities\Setting;

class DiscountService
{
    /**
     * بررسی و اعمال کد تخفیف
     */
    public static function checkDiscount(?string $discount_code)
    {
        if ($discount_code == null) {
            return response()->json([
                'success' => false, 'button' => false,
                'message' => 'کد تخفیف را وارد نمایید.',
            ], 200);
        }
        $basket = Basket::authUser()->orderBy('id', 'DESC')->first();
        if (!$basket) {
            return response()->json([
                'success' => false, 'button' => false,
                'message' => 'سبدی برای کاربر یافت نشد.',
            ], 200);
        }
        $discount = self::findDiscount($discount_code);
        if (!$discount) {
            return response()->json([
                'success' => false, 'button' => false,
                'message' => 'کد تخفیف نامعتبر است.',
            ], 200);
        }
        return self::checkDiscountValidity($basket, $discount);
    }

    /**
     * محاسبه قیمت نهایی با تخفیف و مالیات
     */
    public static function listPrice()
    {
        $basket = Basket::authUser()->latest()->first();
        if (!$basket) {
            return [
                'final_price_sum' => 0, 'price_sum' => 0, 'price_discount' => 0,
                'discount_amount' => 0, 'price_shipping' => 0, 'freight_balance' => 0,
                'price_cart' => 0, 'tax_value' => 0, 'tax' => 0,
            ];
        }

        $discount = $basket->discount;
        $discountCategories = $discount?->productCategories?->pluck('id')->toArray() ?? [];
        $discountBrands = $discount?->brands?->pluck('id')->toArray() ?? [];
        $totalWithoutDiscount = 0;
        $totalDiscounted = 0;
        $discountAmountTotal = 0;
        $weight_sum = 0;

        foreach ($basket->items as $item) {
            $qty = $item->quantity;
            $product = $item->product;
            $variant = $item->product_variant_id ? $item->productVariant : null;
            $price = $variant ? $variant->price : $product->price;
            $finalPrice = $variant ? $variant->final_price : $product->final_price;
            $totalWithoutDiscount += $price * $qty;

            $isDiscountable = false;
            if ($discount) {
                $pay_type = $discount->pay_type;
                if ($pay_type === 'category') {
                    $productCategories = $product->categories->pluck('id')->toArray();
                    $isDiscountable = !empty($discountCategories)
                        && count(array_intersect($productCategories, $discountCategories)) > 0;
                } elseif ($pay_type === 'brand') {
                    $brandId = $product->brand?->id;
                    $isDiscountable = !empty($discountBrands) && in_array($brandId, $discountBrands);
                } elseif ($pay_type === 'product') {
                    $isDiscountable = $basket->items->where('product_id', $product->id)->isNotEmpty();
                }
            }

            $passesWithDiscount = true;
            if ($discount && $isDiscountable) {
                if ($discount->with_discount === 0) {
                    $passesWithDiscount = intval($product->discounted_price) === 0;
                } elseif ($discount->with_discount === 1) {
                    $passesWithDiscount = intval($product->discounted_price) !== 0 || intval($product->price) !== 0;
                }
            }

            if ($discount && $isDiscountable && $passesWithDiscount) {
                if ($discount->type === 'percent') {
                    $discountedPrice = $finalPrice - ($finalPrice * $discount->amount / 100);
                } elseif ($discount->type === 'cash') {
                    $discountedPrice = max(0, $finalPrice - $discount->amount);
                } else {
                    $discountedPrice = $finalPrice;
                }
                $totalDiscounted += $discountedPrice * $qty;
                $discountAmountTotal += ($finalPrice - $discountedPrice) * $qty;
            } else {
                $totalDiscounted += $finalPrice * $qty;
            }

            $weight = $variant ? $variant->weight : $product->weight;
            $weight_sum += $weight * $qty;
        }

        if ($basket->shippingMethod && $basket->shippingMethod->type === 'with_weight') {
            $weight_sum = ceil($weight_sum / 100) * 100;
        }

        $shippingData = self::shipmentMethodPrice($basket, $weight_sum, $totalDiscounted);
        $tax = (int)Setting::where('key', 'tax')->value('value');
        $tax_value = ($totalDiscounted * $tax) / 100;

        return [
            'final_price_sum' => $totalDiscounted,
            'price_sum' => $totalWithoutDiscount,
            'price_discount' => $discountAmountTotal,
            'discount_amount' => $discountAmountTotal,
            'price_shipping' => $shippingData['price'],
            'freight_balance' => $shippingData['freight_balance'],
            'price_cart' => $totalDiscounted + $tax_value + ($shippingData['price'] ?? 0),
            'tax_value' => $tax_value,
            'tax' => $tax,
        ];
    }

    /**
     * محاسبه هزینه ارسال
     */
    public static function shipmentMethodPrice($basket, $weight_sum, $final_price_sum)
    {
        $price = "";
        $freight_balance = 0;
        if (!$basket->shippingMethod) {
            return ['price' => $price, 'freight_balance' => $freight_balance];
        } else {
            if ($basket->shippingMethod->type == "fixed_price") {
                $price = @$basket->shippingMethod->price;
            } elseif ($basket->shippingMethod->type == "with_weight") {
                $price = ($weight_sum / 100) * $basket->shippingMethod->price;
            } else {
                $response = ChaparShipment::quote([
                    'origin' => @$basket->shippingMethod->sender_city->chapar_id,
                    'destination' => @$basket->address->city->chapar_id,
                    'weight' => $weight_sum / 1000,
                    'value' => $final_price_sum . '0',
                    'method' => $basket->shippingMethod->chapar_type,
                ]);
                if ($response['result'] === true) {
                    $price = ($response['objects']['order']['price']['fld_Total_Cost']) / 10 ?? null;
                } else {
                    throw new \InvalidArgumentException($response['message']);
                }
            }
            if ($basket->shippingMethod->freight_balance == 1) {
                $price = 0;
                $freight_balance = 1;
            }
            if ($basket->shippingMethod->price_ceiling != 0 && $final_price_sum > $basket->shippingMethod->price_ceiling) {
                $price = 0;
            }
        }
        return ['price' => $price, 'freight_balance' => $freight_balance];
    }
}
```

---

## ۸.۳ Location Services

### AddressService

```php
<?php
// Modules/Location/Services/AddressService.php

namespace Rahweb\CmsCore\Modules\Location\Services;

use Illuminate\Support\Facades\Auth;
use Rahweb\CmsCore\Modules\Location\Entities\Address;

class AddressService
{
    public static function find()
    {
        $addresses = Address::orderby('id', 'DESC')
            ->authUser()
            ->with(['city', 'state'])
            ->get();
        return ['addresses' => $addresses];
    }

    public static function findOne($address_id)
    {
        return Address::findOrFail($address_id);
    }

    public static function create($user_id, $state_id, $city_id, $address, $postal_code, $receiptor_full_name, $receiptor_mobile)
    {
        Address::create([
            'user_id' => $user_id,
            'state_id' => $state_id,
            'city_id' => $city_id,
            'address' => $address,
            'postal_code' => $postal_code,
            'receiptor_full_name' => $receiptor_full_name,
            'receiptor_mobile' => $receiptor_mobile
        ]);
    }

    public static function update($address_id, $user_id, $state_id, $city_id, $address, $postal_code, $receiptor_full_name, $receiptor_mobile)
    {
        $location = Address::findOrFail($address_id);
        $location->update([
            'user_id' => $user_id,
            'state_id' => $state_id,
            'city_id' => $city_id,
            'address' => $address,
            'postal_code' => $postal_code,
            'receiptor_full_name' => $receiptor_full_name,
            'receiptor_mobile' => $receiptor_mobile
        ]);
    }
}
```

### CityService

```php
<?php
// Modules/Location/Services/CityService.php

namespace Rahweb\CmsCore\Modules\Location\Services;

use Rahweb\CmsCore\Modules\Location\Entities\City;

class CityService
{
    public static function findOne($state_id)
    {
        return City::orderBy('name', 'ASC')->where('state_id', $state_id)->get();
    }
}
```

### StateService

```php
<?php
// Modules/Location/Services/StateService.php

namespace Rahweb\CmsCore\Modules\Location\Services;

use Rahweb\CmsCore\Modules\Location\Entities\State;

class StateService
{
    public static function findAll()
    {
        return State::orderBy('name', 'ASC')->get();
    }
}
```

---

## ۸.۴ Config (تنظیمات درگاه‌ها و روش‌های ارسال)

```php
<?php
// Modules/Order/Config/site.php

return [
    'name' => 'Orders',
    'my_banks' => [
        'zarinPal' => [
            'MerchantId' => ['value' => 'کد مرچنت', 'type' => 'string'],
        ],
        'zibal' => [
            'MerchantId' => ['value' => 'کد مرچنت', 'type' => 'string'],
        ],
        'saderat' => [
            'TerminalID' => ['value' => 'ترمینال آیدی', 'type' => 'string'],
        ],
        'snappay' => [
            'client_id' => ['value' => 'client id', 'type' => 'string'],
            'client_secret' => ['value' => 'client secret', 'type' => 'string'],
            'username' => ['value' => 'username', 'type' => 'string'],
            'password' => ['value' => 'password', 'type' => 'string'],
            'base_url' => ['value' => 'base url', 'type' => 'string'],
        ],
        'sadad' => [
            'MerchantId' => ['value' => 'کد مرچنت', 'type' => 'string'],
            'key' => ['value' => 'کد ترمینال', 'type' => 'string'],
            'TerminalId' => ['value' => 'آی دی ترمینال', 'type' => 'string'],
        ],
        'parsian' => [
            'LoginAccount' => ['value' => 'Login Account', 'type' => 'string'],
            'TerminalId' => ['value' => 'آیدی ترمینال', 'type' => 'string'],
        ],
        'sep' => [
            'TerminalId' => ['value' => 'آی دی ترمینال', 'type' => 'string'],
        ],
        'irandargah' => [
            'MerchantId' => ['value' => 'کد مرچنت', 'type' => 'string'],
        ],
    ],
    'shipping_methods' => [
        'chapar' => [
            'chapar_type' => [
                'value' => 'سرویس چاپار',
                'values' => [
                    'زمینی' => 1,
                    'هوایی' => 6,
                    'پست' => 11,
                    'چاپار پلاس' => 35,
                    'پاکت' => 97,
                ],
                'type' => 'options'
            ],
            'user_name' => ['value' => 'نام کاربری چاپار', 'type' => 'string'],
            'password' => ['value' => 'رمز عبور چاپار', 'type' => 'string'],
        ],
    ],
    'bank_settings' => [
        "62.60.206.26" => ["title" => "Ip(ای پی)"],
        "443" => ["title" => "پورت آدرس برگشت"],
        "3306" => ["title" => "پورت پایگاه داده"],
        "php-laravel (پی اچ پی - لاراول)" => ["title" => "پلتفرم نرم افزار"],
        url('/checkout/finish') => ["title" => "آدرس برگشت برای درگاه زرین پال"],
        url('/checkout/finish-saman') => ["title" => "آدرس برگشت برای درگاه سپ"],
        url('/checkout/finish-sadad') => ["title" => "آدرس برگشت برای درگاه سداد"],
        url('/checkout/finish-snapp-pay') => ["title" => "آدرس برگشت برای درگاه اسنپ پی"],
        url('/checkout/finish-ir-dargah') => ["title" => "آدرس برگشت برای درگاه ایران درگاه"],
        url('/checkout/finish-parsian') => ["title" => "آدرس برگشت برای درگاه پارسیان (سینا)"],
        url('/checkout/finish-zibal') => ["title" => "آدرس برگشت برای درگاه زیبال"],
    ],
];
```

---

## ۸.۵ Jobs (بررسی خودکار وضعیت پرداخت)

### ZarinpalInquiryJob

```php
<?php
// Modules/Order/Jobs/ZarinpalInquiryJob.php

namespace Rahweb\CmsCore\Modules\Order\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rahweb\CmsCore\Modules\General\Helper\Sms;
use Rahweb\CmsCore\Modules\Order\Entities\Bank;
use Rahweb\CmsCore\Modules\Order\Entities\Order;
use Rahweb\CmsCore\Modules\Order\Library\ZarinPal;
use Rahweb\CmsCore\Modules\Order\Services\OrderService;
use Rahweb\CmsCore\Modules\Setting\Entities\Setting;

class ZarinpalInquiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $site_name;

    public function __construct($order, $site_name)
    {
        $this->order = $order;
        $this->site_name = $site_name;
    }

    public function handle()
    {
        DB::purge('mysql');
        Config::set('database.connections.mysql', array_merge(
            config('database.connections.mysql'),
            ["username" => 'root', 'database' => 'cms_' . $this->site_name]
        ));
        DB::reconnect('mysql');

        $my_order = OrderService::byId($this->order);
        if ($my_order) {
            if (!in_array($my_order->order_status, ["paying"])) {
                return;
            }
            $bank = Bank::findOrfail($my_order['bank_id']);
            $merchant = json_decode(@$bank['config'], true)['MerchantId'];
            $authority = json_decode(@$my_order->transaction_info, true)['post']['Authority'];

            $myBank = new ZarinPal($bank, $this->site_name);
            try {
                $check = $myBank->inquiry($authority);
                if ($check['status'] == "failed") {
                    $my_order->update(['order_status' => 'unpaid']);
                } else {
                    $transaction_info = [
                        'post' => ["Authority" => $authority],
                        'verify' => ["RefID" => $check["RefID"]]
                    ];
                    $my_order->update([
                        'transaction_info' => $transaction_info,
                        'order_status' => "paid"
                    ]);
                    OrderService::inventory($my_order);
                    // ارسال پیامک
                    if (@$my_order->user->mobile) {
                        $kavenegar = new Sms();
                        $kavenegar->sendLookup("userBuy", ["token" => "$my_order->id"], $my_order->user->mobile);
                    }
                    $admin_mobile = Setting::where('key', 'admin_mobile')->whereNotNull('value')->first();
                    if ($admin_mobile) {
                        $kavenegar = new Sms();
                        $kavenegar->sendLookup("adminBuy", ["token" => "$my_order->id"], $admin_mobile->value);
                    }
                }
            } catch (\Exception $e) {
                $my_order->update(['order_status' => 'unpaid']);
            }
        }
    }
}
```

---

## ۸.۶ Middleware

### AdminPermission

```
// Modules/User/Http/Middleware/AdminPermission.php
// بررسی دسترسی ادمین کاربر
```

### PanelPermission

```
// Modules/General/Http/Middleware/PanelPermission.php
// بررسی ورود کاربر به پنل
```

### MainMiddleware

```
// Modules/General/Http/Middleware/MainMiddleware.php
// Middleware اصلی سایت
```

---

## ۸.۷ DTOها

### ShippingMethodDTO

```php
<?php
// Modules/Order/DTO/ShippingMethodDTO.php

namespace Rahweb\CmsCore\Modules\Order\DTO;

use Rahweb\CmsCore\Modules\General\Helper\NumberHelper;
use Rahweb\CmsCore\Modules\Order\Http\Requests\ShippingMethodRequest;

class ShippingMethodDTO
{
    protected string $title;
    protected $price;
    protected $price_ceiling = null;
    public bool $status;
    public bool $freight_balance;
    protected array $cities = [];
    protected string|null $user_name;
    protected string|null $password;
    protected string|null $type;
    protected string|null $chapar_type;
    protected string|null $description;
    // sender fields
    protected string $sender_name;
    protected string $sender_company;
    protected string $sender_phone;
    protected string $sender_mobile;
    protected string $sender_email;
    protected string $sender_address;
    protected string $sender_postal_code;
    protected int $sender_city_id;

    // getters ...

    public static function fromRequest(ShippingMethodRequest $request)
    {
        $self = new self();
        $self->title = $request->get('title');
        $self->price = intval(NumberHelper::persian2LatinDigit($request->get('price')));
        $self->price_ceiling = intval(NumberHelper::persian2LatinDigit($request->get('price_ceiling')));
        $self->status = $request->has('status');
        $self->freight_balance = $request->has('freight_balance');
        $self->user_name = @$request->get('user_name');
        $self->password = @$request->get('password');
        $self->type = @$request->get('type');
        $self->chapar_type = @$request->get('chapar_type');
        $self->description = @$request->get('description');
        $self->sender_name = @$request->get('sender_name');
        $self->sender_company = @$request->get('sender_company');
        $self->sender_phone = @$request->get('sender_phone');
        $self->sender_mobile = @$request->get('sender_mobile');
        $self->sender_email = @$request->get('sender_email');
        $self->sender_city_id = @$request->get('sender_city_id');
        $self->sender_address = @$request->get('sender_address');
        $self->sender_postal_code = @$request->get('sender_postal_code');
        if ($request->get('cities')) $self->cities = $request->get('cities');
        return $self;
    }
}
```

### DiscountDTO

```php
<?php
// Modules/Order/DTO/DiscountDTO.php

namespace Rahweb\CmsCore\Modules\Order\DTO;

use Rahweb\CmsCore\Modules\General\Helper\NumberHelper;
use Rahweb\CmsCore\Modules\Order\Http\Requests\DiscountRequest;

class DiscountDTO
{
    protected string $title;
    protected string $max_usage_per_user;
    protected string $basket_minimum_price;
    protected string $type;
    protected string $count;
    protected string $amount;
    public bool $first_purchase;
    public bool $with_discount;
    protected int|null $user_id;
    protected ?array $product_categories;
    protected ?array $brands;
    protected string $pay_type;

    // getters ...

    public static function fromRequest(DiscountRequest $request)
    {
        $self = new self();
        $self->title = $request->get('title');
        $self->max_usage_per_user = intval(NumberHelper::persian2LatinDigit($request->get('max_usage_per_user')));
        $self->basket_minimum_price = intval(NumberHelper::persian2LatinDigit($request->get('basket_minimum_price')));
        $self->count = intval(NumberHelper::persian2LatinDigit($request->get('count')));
        $self->amount = intval(NumberHelper::persian2LatinDigit($request->get('amount')));
        $self->type = $request->get('type');
        $self->user_id = $request->get('user_id');
        $self->product_categories = (array) $request->get('product_categories');
        $self->brands = (array) $request->get('brands');
        $self->first_purchase = $request->has('first_purchase');
        $self->with_discount = $request->has('with_discount');
        $self->pay_type = $request->get('pay_type');
        return $self;
    }
}
```

### OrderShippingStatusDTO

```php
<?php
// Modules/Order/DTO/OrderShippingStatusDTO.php

namespace Rahweb\CmsCore\Modules\Order\DTO;

use Rahweb\CmsCore\Modules\Order\Http\Requests\OrderShippingStatusRequest;

class OrderShippingStatusDTO
{
    protected string $title;
    protected string $color;
    public bool $default;
    public bool $for_send;
    public bool $sending_sms;

    // getters ...

    public static function fromRequest(OrderShippingStatusRequest $request)
    {
        $self = new self();
        $self->title = $request->get('title');
        $self->color = $request->get('color');
        $self->default = $request->has('default');
        $self->for_send = $request->has('for_send');
        $self->sending_sms = $request->has('sending_sms');
        return $self;
    }
}
```

---

## ۸.۸ نکات مهم برای استفاده در پروژه مقصد

### ۱. قیمت‌گذاری
- قیمت‌ها در دیتابیس به **تومان** ذخیره می‌شوند
- API بانک‌ها با **ریال** کار می‌کنند (ضریب ×۱۰)
- در زرین‌پال: `intval($amount . "0")` → تبدیل تومان به ریال

### ۲. سیستم بیعانه (Deposit)
- از تنظیمات سایت (`deposit_price` و `cart_deposit`) خوانده می‌شود
- اگر قیمت سفارش ≥ cart_deposit باشد → فقط بیعانه پرداخت می‌شود
- فیلدهای `deposit_price` و `remaining_price` در orders

### ۳. سیستم مالیات (Tax)
- درصد مالیات از تنظیمات سایت (`tax`) خوانده می‌شود
- `tax_value = (totalDiscounted * tax) / 100`
- `price_cart = totalDiscounted + tax_value + shippingPrice`

### ۴. انواع تخفیف
- `percent`: درصدی
- `cash`: نقدی (تومان)
- `pay_type`: category / brand / product
- `with_discount`: آیا روی محصولات تخفیف‌دار اعمال شود؟
- `first_purchase`: فقط خرید اول

### ۵. سیستم ارسال (۳ نوع)
- **fixed_price**: قیمت ثابت
- **with_weight**: بر اساس وزن (هر ۱۰۰ گرم)
- **chapar**: اتصال به API چاپار

### ۶. جریان سفارش
```
سبد خرید → انتخاب آدرس → انتخاب روش ارسال → پرداخت →
  ↓ (موفق)                    ↓ (ناموفق)
  paid/deposit_paid            unpaid → برگشت موجودی
  ↓
  تاریخچه خودکار (OrderObserver)
  ↓
  ارسال پیامک به کاربر و ادمین
  ↓
  حذف سبد خرید
```

### ۷. ویوها (Frontend)
- از **Blade + Vue.js** (AJAX) استفاده شده
- **Livewire** استفاده نشده
- بخش‌های تعاملی با Vue.js و درخواست‌های AJAX

### ۸. سیستم پیامک
- از سرویس **Kavenegar** استفاده شده
- قالب‌ها: `userBuy`, `adminBuy`, `userChangeStatus`, `sendPostCode`
