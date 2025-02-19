<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    // Метод для создания бронирования
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $property = Property::findOrFail($request->property_id);

        // Проверяем, не заняты ли выбранные даты
        $overlappingBookings = Booking::where('property_id', $property->id)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_date', '<', $request->start_date)
                            ->where('end_date', '>', $request->end_date);
                      });
            })
            ->exists();

        if ($overlappingBookings) {
            return redirect()->back()->with('error', 'Выбранные даты уже заняты. Пожалуйста, выберите другие даты.');
        }

        // Расчет общей стоимости
        $startDate = new \DateTime($request->start_date);
        $endDate = new \DateTime($request->end_date);
        $interval = $startDate->diff($endDate);
        $days = $interval->days;

        $totalPrice = $property->price_per_night * $days;

        // Создание бронирования
        Booking::create([
            'user_id' => Auth::id(),
            'property_id' => $property->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_price' => $totalPrice,
            'status' => 'confirmed',
        ]);
    
        return redirect()->route('bookings.history')->with('success', 'Бронирование успешно создано.');
    }
    
    // Метод для отображения истории бронирований пользователя
    public function history()
    {
        $bookings = Booking::where('user_id', Auth::id())->with('property')->get();
        return view('bookings.history', compact('bookings'));
    }

    public function cancelBooking($id)
    {
        $booking = Booking::findOrFail($id);

        // Проверяем, что текущий пользователь является хозяином бронирования
        if ($booking->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'У вас нет прав для отмены этого бронирования.');
        }

        // Проверяем, можно ли отменить бронирование
        if (!$booking->canBeCancelled()) {
            return redirect()->back()->with('error', 'Невозможно отменить бронирование после даты начала.');
        }

        $booking->status = 'cancelled_by_user';
        $booking->save();

        // Можно добавить логику возврата средств или уведомления

        return redirect()->back()->with('success', 'Бронирование успешно отменено.');
    }

    // Метод для отмены бронирования арендодателем
    public function cancelBookingByLandlord($id)
    {
        $booking = Booking::findOrFail($id);
        $property = $booking->property;

        // Проверяем, что текущий пользователь является хозяином жилья
        if ($property->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'У вас нет прав для отмены этого бронирования.');
        }

        // Проверяем, можно ли отменить бронирование
        if (!$booking->canBeCancelled()) {
            return redirect()->back()->with('error', 'Невозможно отменить бронирование после даты начала.');
        }

        $booking->status = 'cancelled_by_landlord';
        $booking->save();

        // Можно добавить логику уведомления пользователя

        return redirect()->back()->with('success', 'Бронирование успешно отменено.');
    }

    public function landlordBookings()
    {
        $bookings = Booking::whereHas('property', function($query) {
            $query->where('user_id', Auth::id());
        })->with('property', 'user')->get();

        return view('landlord.bookings', compact('bookings'));
    }
}
