<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Отображает форму создания отзыва для указанного объекта.
     *
     * @param int $propertyId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create($propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $userId = Auth::id();

        // Проверяем, имеет ли пользователь завершённое бронирование
        $hasCompletedBooking = Booking::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->where('end_date', '<', now())
            ->where('status', 'confirmed')
            ->exists();

        if (!$hasCompletedBooking) {
            return redirect()->back()->with('error', 'Вы не можете оставить отзыв для этого жилья.');
        }

        // Проверяем, не оставлял ли пользователь уже отзыв
        $alreadyReviewed = Review::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyReviewed) {
            return redirect()->back()->with('error', 'Вы уже оставили отзыв для этого жилья.');
        }

        return view('reviews.create', compact('property'));
    }

    /**
     * Сохраняет новый отзыв.
     *
     * @param Request $request
     * @param int $propertyId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $userId = Auth::id();

        $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        // Повторная проверка завершённого бронирования
        $hasCompletedBooking = Booking::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->where('end_date', '<', now())
            ->where('status', 'confirmed')
            ->exists();

        if (!$hasCompletedBooking) {
            return redirect()->back()->with('error', 'Вы не можете оставить отзыв для этого жилья.');
        }

        // Проверка, что отзыв еще не создан
        $alreadyReviewed = Review::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyReviewed) {
            return redirect()->back()->with('error', 'Вы уже оставили отзыв для этого жилья.');
        }

        Review::create([
            'property_id' => $propertyId,
            'user_id'     => $userId,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
        ]);

        return redirect()->route('properties.show', $propertyId)->with('success', 'Ваш отзыв успешно добавлен.');
    }
}