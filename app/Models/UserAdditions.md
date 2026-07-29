# افزودن به مدل پیش‌فرض User

این فایل کد نیست؛ چون `app/Models/User.php` توسط اسکلت لاراول ساخته می‌شود، این موارد را دستی به آن اضافه کنید:

```php
protected $fillable = [
    'name', 'email', 'password', 'locale', 'theme', // دو فیلد آخر را اضافه کنید
];

public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Order::class);
}

public function isAdmin(): bool
{
    return $this->is_admin ?? false;
}
```

همچنین یک Gate برای دسترسی ادمین در `app/Providers/AppServiceProvider.php` (متد boot) اضافه کنید:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('admin', fn ($user) => $user->isAdmin());
```
