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
    /**
     * Отображение профиля пользователя.
     * Данная функция формирует общую картину бронирований,
     * избранных объектов, а также, если пользователь является арендодателем,
     * дополнительно предоставляет статистику объектов и бронирований.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        // Получаем текущего пользователя
        $user = Auth::user();

        // Получение всех бронирований данного пользователя с загрузкой данных об объекте недвижимости.
        $bookings = Booking::where('user_id', $user->id)
            ->with('property')
            ->get();

        // Получение избранных объектов пользователя с информацией об объектах недвижимости.
        $favorites = Favorite::where('user_id', $user->id)
            ->with('property')
            ->get();

        // Фильтрация завершенных бронирований: 
        // бронирования, где дата выезда меньше текущей даты, выбираем первые 3.
        $pastBookings = $bookings->where('end_date', '<', Carbon::now())->take(3);

        // Если пользователь является арендодателем
        if ($user->role === 'landlord') {
            // Извлекаем все объекты, принадлежащие данному пользователю.
            $properties = Property::where('user_id', $user->id)->get();

            // Общая статистика по объектам: общее количество бронирований и суммарная выручка.
            $totalBookings = Booking::whereIn('property_id', $properties->pluck('id'))->count();
            $totalRevenue = Booking::whereIn('property_id', $properties->pluck('id'))->sum('total_price');

            // Формируем статистику по каждому объекту.
            $propertyBookings = [];
            foreach ($properties as $property) {
                $propertyBookingCount = Booking::where('property_id', $property->id)->count();
                $propertyRevenue = Booking::where('property_id', $property->id)->sum('total_price');

                $propertyBookings[] = [
                    'property'      => $property,
                    'booking_count' => $propertyBookingCount,
                    'revenue'       => $propertyRevenue,
                ];
            }

            // Получение всех бронирований по объектам арендодателя с информацией об объекте.
            $ownerBookings = Booking::whereIn('property_id', $properties->pluck('id'))
                ->with('property')
                ->get();

            // Передаём все собранные данные в представление профиля для арендодателя.
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

        // Для обычных пользователей возвращаем профиль с основными данными.
        return view('user.profile', compact('user', 'bookings', 'favorites', 'pastBookings'));
    }
}