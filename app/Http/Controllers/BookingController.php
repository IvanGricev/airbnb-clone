<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
class BookingController extends Controller
{
    /**
     * Метод для создания бронирования.
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'start_date'  => 'required|date|after_or_equal:today',
            'end_date'    => 'required|date|after:start_date',
        ]);

        $property = Property::findOrFail($request->property_id);

        // Проверка пересечения дат бронирования
        $overlappingBookings = Booking::where('property_id', $property->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<', $request->start_date)
                            ->where('end_date', '>', $request->end_date);
                      });
            })
            ->exists();

        if ($overlappingBookings) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Выбранные даты уже заняты. Пожалуйста, выберите другие даты.'
                ]);
            }
            return redirect()->back()->with('error', 'Выбранные даты уже заняты. Пожалуйста, выберите другие даты.');
        }

        $startDate  = new \DateTime($request->start_date);
        $endDate    = new \DateTime($request->end_date);
        $interval   = $startDate->diff($endDate);
        $days       = $interval->days;
        $totalPrice = $property->price_per_night * $days;

        if ($totalPrice > 99999999.99) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Общая стоимость бронирования превышает максимально допустимое значение.'
                ]);
            }
            return redirect()->back()->with('error', 'Общая стоимость бронирования превышает максимально допустимое значение.');
        }

        $booking = Booking::create([
            'user_id'     => Auth::id(),
            'property_id' => $property->id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'total_price' => $totalPrice,
            'status'      => 'pending_payment',
        ]);
    
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'bookingId' => $booking->id,
                'totalPrice' => $totalPrice,
                'paymentUrl' => route('payments.process', $booking->id)
            ]);
        }

        return redirect()->route('payments.checkout', $booking->id)
                         ->with('success', 'Бронирование создано. Пожалуйста, оплатите его.');
    }

    /**
     * Отображение истории бронирований пользователя с пагинацией и кэшированием.
     */
    public function history()
    {
        $bookings = Cache::remember('user_bookings_' . Auth::id(), now()->addMinutes(10), function(){
            return Booking::where('user_id', Auth::id())
                ->with('property')
                ->paginate(10);
        });
    
        return view('bookings.history', compact('bookings'));
    }

    /**
     * Отмена бронирования пользователем.
     */
    public function cancelBooking($id)
    {
        $booking = Booking::findOrFail($id);

        // Логгирование текущего статуса для отладки
        Log::info("Пользователь " . Auth::id() . " пытается отменить бронирование #" . $booking->id . ", текущий статус: " . $booking->status);

        if ($booking->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'У вас нет прав для отмены этого бронирования.');
        }

        if (!$booking->canBeCancelled()) {
            return redirect()->back()->with('error', 'Невозможно отменить бронирование после даты начала.');
        }

        $booking->status = 'cancelled_by_user';
        Log::info("Устанавливаем статус бронирования #" . $booking->id . " в 'cancelled_by_user'");
        $booking->save();
        Log::info("После сохранения, новый статус бронирования #" . $booking->id . ": " . $booking->status);

        return redirect()->back()->with('success', 'Бронирование успешно отменено.');
    }

    /**
     * Отмена бронирования арендодателем.
     */
    public function cancelBookingByLandlord($id)
    {
        $booking  = Booking::findOrFail($id);
        $property = $booking->property;

        Log::info("Арендодатель " . Auth::id() . " пытается отменить бронирование #" . $booking->id . " для объекта #" . $property->id);

        if ($property->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'У вас нет прав для отмены этого бронирования.');
        }

        if (!$booking->canBeCancelled()) {
            return redirect()->back()->with('error', 'Невозможно отменить бронирование после даты начала.');
        }

        $booking->status = 'cancelled_by_landlord';
        Log::info("Устанавливаем статус бронирования #" . $booking->id . " в 'cancelled_by_landlord'");
        $booking->save();
        Log::info("После сохранения, новый статус бронирования #" . $booking->id . ": " . $booking->status);

        return redirect()->back()->with('success', 'Бронирование успешно отменено.');
    }

    /**
     * Отображает бронирования для арендодателя с пагинацией и кэшированием.
     */
    public function landlordBookings()
    {
        $bookings = Cache::remember('landlord_bookings_' . Auth::id(), now()->addMinutes(10), function () {
            return Booking::whereHas('property', function ($query) {
                $query->where('user_id', Auth::id());
            })->with(['property', 'user'])
              ->paginate(10);
        });

        return view('landlord.bookings', compact('bookings'));
    }
}