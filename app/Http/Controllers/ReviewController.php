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
     * Отображает форму создания отзыва для конкретного объекта недвижимости.
     *
     * @param int $propertyId Идентификатор объекта недвижимости.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create($propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $userId = Auth::id();
        
        // Проверяем, имеет ли пользователь завершенное бронирование объекта.
        $hasCompletedBooking = Booking::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->where('end_date', '<', now())
            ->where('status', 'confirmed')
            ->exists();
        
        if (!$hasCompletedBooking) {
            return redirect()->back()->with('error', 'Вы не можете оставить отзыв для этого жилья.');
        }
        
        // Проверяем, не оставлял ли уже пользователь отзыв для этого объекта.
        $alreadyReviewed = Review::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->exists();
        
        if ($alreadyReviewed) {
            return redirect()->back()->with('error', 'Вы уже оставили отзыв для этого жилья.');
        }
        
        return view('reviews.create', compact('property'));
    }

    /**
     * Сохраняет новый отзыв для объекта недвижимости.
     *
     * @param Request $request
     * @param int $propertyId Идентификатор объекта недвижимости.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $userId = Auth::id();

        // Валидация входных данных.
        // Поле rating должно содержать целое число от 1 до 5.
        // Поле comment является необязательным, но если задано, то должно быть строкой.
        $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        // Проверяем, имеет ли пользователь завершенное бронирование для этого объекта.
        $hasCompletedBooking = Booking::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->where('end_date', '<', now())
            ->where('status', 'confirmed')
            ->exists();

        if (!$hasCompletedBooking) {
            return redirect()->back()->with('error', 'Вы не можете оставить отзыв для этого жилья.');
        }
        
        // Проверяем, что пользователь еще не оставил отзыв для этого объекта.
        $alreadyReviewed = Review::where('property_id', $propertyId)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyReviewed) {
            return redirect()->back()->with('error', 'Вы уже оставили отзыв для этого жилья.');
        }

        // Создаем новый отзыв.
        Review::create([
            'property_id' => $propertyId,
            'user_id'     => $userId,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
        ]);

        return redirect()->route('properties.show', $propertyId)
                         ->with('success', 'Ваш отзыв успешно добавлен.');
    }
}