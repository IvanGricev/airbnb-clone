<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    /**
     * Отображает форму оплаты бронирования.
     */
    public function showPaymentForm($bookingId)
    {
        $booking = Cache::remember('booking_' . $bookingId, now()->addMinutes(10), function () use ($bookingId) {
            return Booking::with('property')->findOrFail($bookingId);
        });

        if (Auth::id() !== $booking->user_id) {
            return redirect()->back()->with('error', 'У вас нет прав для оплаты этого бронирования.');
        }

        return view('payments.payment_form', compact('booking'));
    }

    /**
     * Обрабатывает симуляцию оплаты.
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

        // Симуляция оплаты: обновление статуса платежа и бронирования.
        if (isset($booking->payment_status)) {
            $booking->payment_status = 'paid';
        }
        $booking->status = 'confirmed';
        $booking->save();

        return redirect()->route('bookings.history')
                         ->with('success', 'Платеж успешно произведен (симуляция).');
    }
}
