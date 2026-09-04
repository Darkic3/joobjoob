<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $review->update(['is_approved' => (int) $request->is_approved]);

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
