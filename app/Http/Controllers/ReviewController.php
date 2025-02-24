<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Отображение формы создания отзыва
    public function create($propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Проверяем, что пользователь имеет право оставлять отзыв
        // Он должен иметь завершённое бронирование этого жилья
        $userId = Auth::id();
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

    // Сохранение отзыва
    public function store(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $userId = Auth::id();

        // Проверяем, что пользователь имеет право оставлять отзыв
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

        Review::create([
            'property_id' => $propertyId,
            'user_id' => $userId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('properties.show', $propertyId)->with('success', 'Ваш отзыв успешно добавлен.');
    }
}
