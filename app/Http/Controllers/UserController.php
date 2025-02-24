<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Favorite;
use App\Models\Review;
use Carbon\Carbon;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        // Получение бронирований пользователя
        $bookings = Booking::where('user_id', $user->id)
            ->with('property')
            ->get();

        // Получение избранных объектов
        $favorites = Favorite::where('user_id', $user->id)
            ->with('property')
            ->get();

        // Получение ранее арендованных объектов (завершенных бронирований)
        $pastBookings = $bookings->where('end_date', '<', Carbon::now())->take(3);

        // Если пользователь - арендодатель
        if ($user->role === 'landlord') {
            // Получение объектов пользователя
            $properties = Property::where('user_id', $user->id)->get();

            // Общая статистика
            $totalBookings = Booking::whereIn('property_id', $properties->pluck('id'))->count();
            $totalRevenue = Booking::whereIn('property_id', $properties->pluck('id'))->sum('total_price');

            // Бронирования по каждому объекту
            $propertyBookings = [];
            foreach ($properties as $property) {
                $propertyBookingCount = Booking::where('property_id', $property->id)->count();
                $propertyRevenue = Booking::where('property_id', $property->id)->sum('total_price');

                $propertyBookings[] = [
                    'property' => $property,
                    'booking_count' => $propertyBookingCount,
                    'revenue' => $propertyRevenue,
                ];
            }

            // Получение бронирований по объектам арендодателя
            $ownerBookings = Booking::whereIn('property_id', $properties->pluck('id'))
                ->with('property')
                ->get();

            // Передаём $ownerBookings в представление
            return view('user.profile', compact(
                'user',
                'bookings',
                'favorites',
                'pastBookings',
                'totalBookings',
                'totalRevenue',
                'propertyBookings',
                'ownerBookings'
            ));
        }

        // Для обычных пользователей
        return view('user.profile', compact('user', 'bookings', 'favorites', 'pastBookings'));
    }
}
