<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Отображаем форму оплаты для бронирования.
     *
     * @param int $bookingId
     * @return \Illuminate\View\View
     */
    public function showPaymentForm($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // Проверяем, что текущий пользователь является владельцем бронирования
        if (Auth::id() !== $booking->user_id) {
            return redirect()->back()->with('error', 'У вас нет прав для оплаты этого бронирования.');
        }

        // Здесь можно добавить проверку: если уже оплачено, выводить сообщение

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

        if (Auth::id() !== $booking->user_id) {
            return redirect()->back()->with('error', 'У вас нет прав для оплаты этого бронирования.');
        }

        $request->validate([
            'card_number'     => 'required|digits:16',
            'expiration_date' => 'required|date_format:m/y',
            'cvv'             => 'required|digits:3',
        ]);

        // Симулируем процесс оплаты.
        //
        // В реальном приложении здесь вы бы интегрировали платежный шлюз.
        // Для симуляции считаем, что плата прошла успешно.

        // Если у вас есть поле payment_status в таблице bookings, можно его обновить:
        if (isset($booking->payment_status)) {
            $booking->payment_status = 'paid';
        }
        // Если нет, можно, например, добавить лог или уведомление.
        $booking->save();

        return redirect()->route('bookings.history')
            ->with('success', 'Платеж успешно произведен (симуляция).');
    }
}
