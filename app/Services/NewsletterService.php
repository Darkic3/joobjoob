<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;

/**
 * سرویس خبرنامه — منبع حقیقت جدول newsletter_subscribers است.
 *
 * فعال/غیرفعال بودن کلی خبرنامه با تنظیمات ادمین (newsletter_enabled)
 * کنترل می‌شود؛ وقتی خاموش است هیچ عضویتی ثبت یا فعال نمی‌شود.
 */
class NewsletterService
{
    public function isEnabled(): bool
    {
        return (bool) setting('newsletter_enabled', false);
    }

    public function isSubscribed(?string $email): bool
    {
        if (! $email) {
            return false;
        }

        return NewsletterSubscriber::where('email', mb_strtolower(trim($email)))
            ->active()
            ->exists();
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function subscribe(string $email, ?int $userId = null): array
    {
        if (! $this->isEnabled()) {
            return ['success' => false, 'message' => __('messages.newsletter_disabled')];
        }

        $email = mb_strtolower(trim($email));

        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if ($subscriber) {
            $subscriber->update([
                'is_active' => true,
                'user_id' => $userId ?? $subscriber->user_id,
            ]);
        } else {
            NewsletterSubscriber::create([
                'email' => $email,
                'user_id' => $userId,
                'is_active' => true,
            ]);
        }

        return ['success' => true, 'message' => __('messages.newsletter_subscribed')];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function unsubscribe(?string $email = null, ?int $userId = null): array
    {
        $query = NewsletterSubscriber::query();

        if ($email) {
            $query->where('email', mb_strtolower(trim($email)));
        } elseif ($userId) {
            $query->where('user_id', $userId);
        } else {
            return ['success' => false, 'message' => __('messages.newsletter_error')];
        }

        $query->update(['is_active' => false]);

        return ['success' => true, 'message' => __('messages.newsletter_unsubscribed')];
    }
}
