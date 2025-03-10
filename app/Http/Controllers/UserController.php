<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Favorite;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * Отображает профиль пользователя с историей бронирований, избранными объектами
     * и, если пользователь – арендодатель, статистикой по объектам.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = Auth::user();

        // Кэширование бронирований пользователя
        $bookings = Cache::remember('user_bookings_' . $user->id, now()->addMinutes(10), function () use ($user) {
            return Booking::where('user_id', $user->id)
                ->with('property')
                ->get();
        });

        $favorites = Cache::remember('user_favorites_' . $user->id, now()->addMinutes(10), function () use ($user) {
            return Favorite::where('user_id', $user->id)
                ->with('property')
                ->get();
        });

        $pastBookings = $bookings->where('end_date', '<', Carbon::now())->take(3);

        if ($user->role === 'landlord') {
            $properties = Property::where('user_id', $user->id)->get();

            $totalBookings = Booking::whereIn('property_id', $properties->pluck('id'))->count();
            $totalRevenue  = Booking::whereIn('property_id', $properties->pluck('id'))->sum('total_price');

            $propertyBookings = [];
            foreach ($properties as $property) {
                $propertyBookings[] = [
                    'property'      => $property,
                    'booking_count' => Booking::where('property_id', $property->id)->count(),
                    'revenue'       => Booking::where('property_id', $property->id)->sum('total_price'),
                ];
            }

            $ownerBookings = Cache::remember('owner_bookings_' . $user->id, now()->addMinutes(10), function () use ($properties) {
                return Booking::whereIn('property_id', $properties->pluck('id'))
                    ->with('property')
                    ->get();
            });

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

        return view('user.profile', compact('user', 'bookings', 'favorites', 'pastBookings'));
    }
}
