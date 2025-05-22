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

        return view('reviews.create', compact('property'));
    }

    /**
     * Сохраняет новый отзыв.
     * @param Request $request
     * @param int $propertyId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $propertyId)
    {
        try {
            $property = Property::findOrFail($propertyId);
            $userId = Auth::id();

            $messages = [
                'rating.required' => 'Оценка обязательна для заполнения.',
                'rating.integer' => 'Оценка должна быть целым числом.',
                'rating.between' => 'Оценка должна быть от 1 до 5.',
                'comment.string' => 'Комментарий должен быть текстом.',
                'comment.max' => 'Комментарий не должен превышать 1000 символов.',
            ];

            $validatedData = $request->validate([
                'rating' => 'required|integer|between:1,5',
                'comment' => 'nullable|string|max:1000',
            ], $messages);

            // Проверяем, не оставлял ли пользователь уже отзыв
            $existingReview = Review::where('property_id', $propertyId)
                ->where('user_id', $userId)
                ->exists();

            if ($existingReview) {
                return redirect()->back()
                    ->withErrors(['error' => 'Вы уже оставили отзыв для этого жилья.'])
                    ->withInput();
            }

            // Повторная проверка завершённого бронирования
            $hasCompletedBooking = Booking::where('property_id', $propertyId)
                ->where('user_id', $userId)
                ->where('end_date', '<', now())
                ->where('status', 'confirmed')
                ->exists();

            if (!$hasCompletedBooking) {
                return redirect()->back()
                    ->withErrors(['error' => 'Вы не можете оставить отзыв для этого жилья.'])
                    ->withInput();
            }

            Review::create([
                'property_id' => $propertyId,
                'user_id' => $userId,
                'rating' => $validatedData['rating'],
                'comment' => $validatedData['comment'],
            ]);

            return redirect()->route('properties.show', $propertyId)
                ->with('success', 'Ваш отзыв успешно добавлен.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Произошла ошибка при сохранении отзыва. Пожалуйста, попробуйте снова.'])
                ->withInput();
        }
    }
}