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
            'start_date.date_format' => 'Некорректный формат даты заезда. Используйте ДД.ММ.ГГГГ.',
            'start_date.after_or_equal' => 'Дата заезда не может быть в прошлом.',
            'end_date.required' => 'Дата выезда обязательна для заполнения.',
            'end_date.date_format' => 'Некорректный формат даты выезда. Используйте ДД.ММ.ГГГГ.',
            'end_date.after' => 'Дата выезда должна быть позже даты заезда.',
        ];

        // Валидация с учетом текстового поля и формата дд.мм.гггг
        $validatedData = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date_format:d.m.Y|after_or_equal:today',
            'end_date' => 'required|date_format:d.m.Y|after:start_date',
        ], $messages);

        try {
            $property = Property::findOrFail($validatedData['property_id']);

            // Преобразуем даты из дд.мм.гггг в Y-m-d для работы с Carbon и БД
            $startDate = \Carbon\Carbon::createFromFormat('d.m.Y', $validatedData['start_date']);
            $endDate = \Carbon\Carbon::createFromFormat('d.m.Y', $validatedData['end_date']);

            // Проверка пересечения с другими бронированиями
            $overlappingBookings = Booking::where('property_id', $property->id)
                ->whereNotIn('status', ['cancelled_by_user', 'cancelled_by_landlord'])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                          ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                          ->orWhere(function ($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<', $startDate->format('Y-m-d'))
                                ->where('end_date', '>', $endDate->format('Y-m-d'));
                          });
                })
                ->exists();

            if ($overlappingBookings) {
                return redirect()->back()
                    ->withErrors(['dates' => 'Выбранные даты уже заняты. Пожалуйста, выберите другие даты.'])
                    ->withInput();
            }

            $days = $endDate->diffInDays($startDate);
            if ($days < 0) {
                $days = abs($days);
            }

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
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_price' => $totalPrice,
                'status' => 'pending_payment',
            ]);

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