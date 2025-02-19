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
        ]);

        return redirect()->route('bookings.history')->with('success', 'Бронирование успешно создано.');
    }

    // Метод для отображения истории бронирований пользователя
    public function history()
    {
        $bookings = Booking::where('user_id', Auth::id())->with('property')->get();
        return view('bookings.history', compact('bookings'));
    }
}
