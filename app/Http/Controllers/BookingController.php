<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Метод для создания бронирования.
     */
    public function store(Request $request)
    {
        $messages = [
            'property_id.required' => 'Необходимо указать объект недвижимости.',
            'property_id.exists' => 'Выбранный объект недвижимости не существует.',
            'start_date.required' => 'Дата заезда обязательна для заполнения.',
            'start_date.date' => 'Некорректный формат даты заезда.',
            'start_date.after_or_equal' => 'Дата заезда не может быть в прошлом.',
            'end_date.required' => 'Дата выезда обязательна для заполнения.',
            'end_date.date' => 'Некорректный формат даты выезда.',
            'end_date.after' => 'Дата выезда должна быть позже даты заезда.',
        ];

        $validatedData = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ], $messages);

        try {
            $property = Property::findOrFail($validatedData['property_id']);

            // Проверка пересечения с другими бронированиями
            $overlappingBookings = Booking::where('property_id', $property->id)
                ->whereNotIn('status', ['cancelled_by_user', 'cancelled_by_landlord'])
                ->where(function ($query) use ($validatedData) {
                    $query->whereBetween('start_date', [$validatedData['start_date'], $validatedData['end_date']])
                          ->orWhereBetween('end_date', [$validatedData['start_date'], $validatedData['end_date']])
                          ->orWhere(function ($q) use ($validatedData) {
                              $q->where('start_date', '<', $validatedData['start_date'])
                                ->where('end_date', '>', $validatedData['end_date']);
                          });
                })
                ->exists();

            if ($overlappingBookings) {
                return redirect()->back()
                    ->withErrors(['dates' => 'Выбранные даты уже заняты. Пожалуйста, выберите другие даты.'])
                    ->withInput();
            }

            $startDate = Carbon::parse($validatedData['start_date']);
            $endDate = Carbon::parse($validatedData['end_date']);
            $days = $endDate->diffInDays($startDate);
            
            // Убедимся, что количество дней положительное
            if ($days < 0) {
                $days = abs($days);
            }
            
            // Убедимся, что цена за ночь положительная
            $pricePerNight = abs($property->price_per_night);
            $totalPrice = $pricePerNight * $days;

            if ($totalPrice > 99999999.99) {
                return redirect()->back()
                    ->withErrors(['price' => 'Общая стоимость бронирования превышает максимально допустимое значение.'])
                    ->withInput();
            }

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'property_id' => $property->id,
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'total_price' => $totalPrice,
                'status' => 'pending_payment',
            ]);

            // Очищаем кэш бронирований пользователя
            Cache::forget('user_bookings_' . Auth::id());

            return redirect()->route('payments.checkout', $booking->id)
                           ->with('success', 'Бронирование создано. Пожалуйста, оплатите его.');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании бронирования', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'property_id' => $request->property_id,
                'dates' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date
                ]
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Произошла ошибка при создании бронирования. Пожалуйста, попробуйте снова.'])
                ->withInput();
        }
    }

    /**
     * Отображение истории бронирований пользователя с пагинацией и кэшированием.
     */
    public function history()
    {
        // Очищаем кэш перед получением данных
        Cache::forget('user_bookings_' . Auth::id());
        
        $bookings = Booking::where('user_id', Auth::id())
            ->with('property')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    
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