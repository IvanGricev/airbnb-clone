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
use Illuminate\Support\Facades\Hash;

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

    public function updateNameEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        try {
            $user = Auth::user();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            return redirect()->route('user.profile')->with('success', 'Данные успешно обновлены!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Не удалось обновить данные.');
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return redirect()->back()->with('error', 'Текущий пароль введен неверно.');
        }

        try {
            $user = Auth::user();
            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->route('user.profile')->with('success', 'Пароль успешно изменён!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Не удалось изменить пароль.');
        }
    }
}
