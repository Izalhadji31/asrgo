<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\NotificationLog;
use App\Models\Review;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorize('review', $booking);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'customer_id' => Auth::id(),
            'rating' => (int) $validated['rating'],
            'komentar' => trim($validated['komentar'] ?? '') ?: null,
        ]);

        $customerName = $booking->pelanggan?->name ?? 'Customer #'.$booking->pelanggan_id;

        foreach (User::where('role', 'admin')->pluck('id') as $adminId) {
            $this->notificationService->log(
                $adminId,
                'review_created',
                'Ulasan baru bintang '.$review->rating.' untuk booking #'.$booking->id.' dari '.$customerName.'.',
                Review::class,
                $review->id
            );
        }

        return back()->with('success', 'Terima kasih, ulasan Anda sudah tersimpan.');
    }

    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $totalReviews = Review::count();
        $avgRating = round((float) Review::avg('rating'), 1);

        $reviews = Review::with(['booking.vehicle', 'booking.pelanggan', 'customer'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'totalReviews', 'avgRating'));
    }

    public function destroy(Review $review)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        NotificationLog::where('related_model', Review::class)
            ->where('related_id', $review->id)
            ->delete();

        $reviewId = $review->id;
        $bookingId = $review->booking_id;
        $review->delete();

        AuditLog::record('delete_review', 'Menghapus ulasan #'.$reviewId.' untuk booking #'.$bookingId, Review::class, $reviewId);

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
