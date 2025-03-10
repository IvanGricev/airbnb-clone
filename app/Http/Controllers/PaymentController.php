<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Отображает форму оплаты для бронирования.
     *
     * @param int $bookingId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showPaymentForm($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // Проверяем, что текущий пользователь является владельцем бронирования.
        if (Auth::id() !== $booking->user_id) {
            return redirect()->back()->with('error', 'У вас нет прав для оплаты этого бронирования.');
        }

        // При необходимости добавьте проверку для уже оплаченных бронирований:
        // if ($booking->payment_status === 'paid') {
        //     return redirect()->back()->with('info', 'Бронирование уже оплачено.');
        // }

        return view('payments.payment_form', compact('booking'));
    }

    /**
     * Обработка симуляции оплаты.
     *
     * @param Request $request
     * @param int $bookingId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPayment(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // Проверяем, что текущий пользователь является владельцем бронирования.
        if (Auth::id() !== $booking->user_id) {
            return redirect()->back()->with('error', 'У вас нет прав для оплаты этого бронирования.');
        }

        // Валидация входных данных.
        // Требования: номер карты должен состоять ровно из 16 цифр, дата в формате "м/гг" и CVV из 3 цифр.
        $request->validate([
            'card_number'     => 'required|digits:16',
            'expiration_date' => 'required|date_format:m/y',
            'cvv'             => 'required|digits:3',
        ]);

        // Симуляция процесса оплаты:
        // Здесь можно интегрировать платежный шлюз. На данном этапе симулируется успешная оплата.
        
        // Обновляем статус платежа. Если в таблице bookings есть поле payment_status, используем его.
        if (isset($booking->payment_status)) {
            $booking->payment_status = 'paid';
        }

        // Дополнительно, можно обновить статус бронирования на "confirmed", если требуется:
        $booking->status = 'confirmed';
        $booking->save();

        return redirect()->route('bookings.history')
            ->with('success', 'Платеж успешно произведен (симуляция).');
    }
}