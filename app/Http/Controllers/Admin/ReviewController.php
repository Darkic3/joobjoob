<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::with('product', 'user', 'parent')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                        ->orWhere('guest_name', 'like', "%{$search}%")
                        ->orWhere('guest_phone', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('product', function ($q2) use ($search) {
                            $q2->where('name_fa', 'like', "%{$search}%")
                                ->orWhere('name_en', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_approved', (int) $request->input('status'));
            })
            ->when($request->filled('rating'), function ($query) use ($request) {
                $query->where('rating', $request->input('rating'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.list', compact('reviews'));
    }

    public function show(Review $review): View
    {
        $review->load('product', 'user', 'replies.user');

        return view('admin.reviews.show', compact('review'));
    }

    /**
     * تغییر وضعیت تأیید نظر (AJAX)
     */
    public function updateApproval(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:reviews,id',
            'is_approved' => 'required|integer|in:0,1,2',
        ]);

        $review = Review::findOrFail($request->id);
        $wasApproved = (int) $review->is_approved === Review::STATUS_APPROVED;
        $review->update(['is_approved' => (int) $request->is_approved]);

        // اعلان تأیید برای نویسندهٔ نظر / نویسندهٔ نظرِ والدِ پاسخ
        $this->sendApprovalNotifications($review, $wasApproved);

        $messages = [
            Review::STATUS_APPROVED => __('admin.reviews.approved'),
            Review::STATUS_REJECTED => __('admin.reviews.rejected'),
            Review::STATUS_PENDING  => __('admin.reviews.pending'),
        ];

        return response()->json([
            'success' => true,
            'message' => $messages[(int) $request->is_approved] ?? __('admin.reviews.updated'),
        ]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', __('admin.reviews.deleted'));
    }

    /**
     * پاسخ ادمین به نظر کاربر (AJAX)
     */
    public function reply(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'review_id' => 'required|integer|exists:reviews,id',
            'comment'   => 'required|string|max:2000',
        ]);

        $parentReview = Review::findOrFail($request->review_id);

        $reply = Review::create([
            'product_id'  => $parentReview->product_id,
            'user_id'     => auth()->id(),
            'parent_id'   => $parentReview->id,
            'rating'      => $parentReview->rating,
            'comment'     => $request->comment,
            'is_approved' => Review::STATUS_APPROVED,
        ]);

        // اعلان «پاسخ جدید» برای نویسندهٔ نظر والد — پاسخ ادمین بلافاصله تأیید و قابل‌نمایش است
        $this->sendReplyNotification($reply);

        return response()->json([
            'success' => true,
            'message' => __('admin.reviews.reply_sent'),
            'reply'   => [
                'id'           => $reply->id,
                'display_name' => $reply->display_name,
                'comment'      => $reply->comment,
                'created_at'   => $reply->created_at->diffForHumans(),
            ],
        ]);
    }

    // ==================== اعلان‌های نظر ====================

    /**
     * اعلان‌های مربوط به تأیید نظر — بعد از تغییر وضعیت.
     *
     * نظر اصلی تأیید شد → «نظر شما تأیید شد» برای نویسندهٔ نظر.
     * پاسخ تأیید شد (و نظر والد قابل‌نمایش است) → «پاسخ جدید برای نظر شما» برای نویسندهٔ نظر والد.
     * فقط روی گذار «غیرتأیید → تأیید» اعلان ساخته می‌شود تا toggle تکراری هرزنام نفرستد.
     */
    private function sendApprovalNotifications(Review $review, bool $wasApproved): void
    {
        try {
            if ($wasApproved || (int) $review->is_approved !== Review::STATUS_APPROVED) {
                return;
            }

            $service = app(NotificationService::class);

            if ($review->parent_id !== null) {
                // ── پاسخ: اعلان به نویسندهٔ نظر والد ──
                $parent = $review->parent;
                $author = $parent?->user;

                if ($author
                    && (int) $parent->is_approved === Review::STATUS_APPROVED
                    && (int) $author->id !== (int) auth()->id()) {
                    $this->notifyReplyPosted($service, $author, $review);
                }

                return;
            }

            // ── نظر اصلی: اعلان به نویسندهٔ نظر ──
            $author = $review->user;

            if ($author && (int) $author->id !== (int) auth()->id()) {
                $this->notifyReviewApproved($service, $author, $review);
            }

            // اگر این نظر پاسخ‌های تأییدشده دارد که با تأیید والد اکنون قابل‌نمایش می‌شوند،
            // اعلان «پاسخ جدید» هم ارسال می‌شود تا ترتیب تأیید مهم نباشد.
            foreach ($review->replies as $reply) {
                $this->notifyReplyPosted($service, $review->user, $reply);
            }
        } catch (\Throwable $e) {
            Log::error('Review approval notification failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * اعلان «پاسخ جدید» پس از ثبت پاسخ ادمین — فقط وقتی نظر والد تأیید و قابل‌نمایش است.
     */
    private function sendReplyNotification(Review $reply): void
    {
        try {
            $parent = $reply->parent;
            $author = $parent?->user;

            if (! $author
                || (int) $parent->is_approved !== Review::STATUS_APPROVED
                || (int) $author->id === (int) auth()->id()) {
                return;
            }

            $this->notifyReplyPosted(app(NotificationService::class), $author, $reply);
        } catch (\Throwable $e) {
            Log::error('Review reply notification failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * اعلان «نظر شما تأیید شد» برای نویسندهٔ نظر (با جلوگیری از ارسال تکراری).
     */
    private function notifyReviewApproved(NotificationService $service, User $author, Review $review): void
    {
        $alreadyNotified = Notification::where('type', 'review.approved')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $author->id)
            ->where('data->review_id', $review->id)
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $service->notifyUser(
            $author,
            'review.approved',
            'نظر شما تأیید شد',
            null,
            $this->reviewLink($review->product),
            ['product_id' => $review->product_id, 'review_id' => $review->id, 'product_name' => $this->productName($review->product)]
        );
    }

    /**
     * اعلان «پاسخ جدید برای نظر شما» برای نویسندهٔ نظر والد (با جلوگیری از ارسال تکراری).
     */
    private function notifyReplyPosted(NotificationService $service, ?User $author, Review $reply): void
    {
        if (! $author || (int) $author->id === (int) auth()->id()) {
            return;
        }

        $alreadyNotified = Notification::where('type', 'review.replied')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $author->id)
            ->where('data->reply_id', $reply->id)
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $service->notifyUser(
            $author,
            'review.replied',
            'پاسخ جدید برای نظر شما',
            null,
            $this->reviewLink($reply->product),
            ['product_id' => $reply->product_id, 'reply_id' => $reply->id, 'product_name' => $this->productName($reply->product)]
        );
    }

    /**
     * لینک اعلان — صفحهٔ محصول، لنگر تب نظرات.
     */
    private function reviewLink(?\App\Models\Product $product): string
    {
        return $product
            ? route('products.show', $product->slug).'#tab-reviews'
            : route('products.index');
    }

    /**
     * نام محصول به زبان جاری — با جایگزین امن.
     */
    private function productName(?\App\Models\Product $product): string
    {
        return $product?->trans('name') ?? $product?->name ?? 'محصول';
    }

    /**
     * تغییر وضعیت نمایش در صفحه اول (AJAX)
     */
    public function toggleFeatured(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:reviews,id',
        ]);

        $review = Review::findOrFail($request->id);
        $review->update(['is_featured' => ! $review->is_featured]);

        return response()->json([
            'success' => true,
            'is_featured' => $review->is_featured,
            'message' => $review->is_featured
                ? __('admin.reviews.featured_on')
                : __('admin.reviews.featured_off'),
        ]);
    }
}
