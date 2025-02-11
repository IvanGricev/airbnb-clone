<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    // Создание бронирования
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $property = Property::find($request->property_id);

        // Рассчитываем общую стоимость
        $days = (strtotime($request->end_date) - strtotime($request->start_date)) / 86400;
        $totalPrice = $days * $property->price_per_night;

        $booking = new Booking();
        $booking->user_id = Auth::id();
        $booking->property_id = $property->id;
        $booking->start_date = $request->start_date;
        $booking->end_date = $request->end_date;
        $booking->total_price = $totalPrice;
        $booking->save();

        return redirect()->route('bookings.show', $booking)->with('success', 'Бронирование успешно создано.');
    }

    // Отображение информации о бронировании
    public function show(Booking $booking)
    {
        // Проверка прав доступа
        if ($booking->user_id !== Auth::id() && $booking->property->user_id !== Auth::id()) {
            return redirect()->route('home')->with('error', 'У вас нет доступа к этому бронированию.');
        }

        return view('bookings.show', compact('booking'));
    }

    public function history()
    {
        $bookings = Booking::where('user_id', Auth::id())->with('property')->orderBy('start_date', 'desc')->get();
        return view('bookings.history', compact('bookings'));
    }

}
