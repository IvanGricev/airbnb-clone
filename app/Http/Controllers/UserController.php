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

        // Получаем избранные объекты без кэширования для актуальности
        $favorites = Favorite::where('user_id', $user->id)
            ->with('property')
            ->get();

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
        $messages = [
            'name.required' => 'Имя обязательно для заполнения.',
            'name.string' => 'Имя должно быть текстом.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.email' => 'Введите корректный email адрес.',
            'email.max' => 'Email не должен превышать 255 символов.',
            'email.unique' => 'Этот email уже используется другим пользователем.',
        ];

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ], $messages);

        try {
            $user = Auth::user();
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->save();

            return redirect()->route('user.profile')
                ->with('success', 'Данные успешно обновлены!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Не удалось обновить данные. Пожалуйста, попробуйте снова.'])
                ->withInput();
        }
    }

    public function updatePassword(Request $request)
    {
        $messages = [
            'current_password.required' => 'Текущий пароль обязателен для заполнения.',
            'new_password.required' => 'Новый пароль обязателен для заполнения.',
            'new_password.string' => 'Новый пароль должен быть текстом.',
            'new_password.min' => 'Новый пароль должен содержать минимум 8 символов.',
            'new_password.confirmed' => 'Пароли не совпадают.',
            'new_password.regex' => 'Пароль должен содержать как минимум одну заглавную букву, одну строчную букву и одну цифру.',
        ];

        $validatedData = $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
        ], $messages);

        if (!Hash::check($validatedData['current_password'], Auth::user()->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Текущий пароль введен неверно.'])
                ->withInput();
        }

        try {
            $user = Auth::user();
            $user->password = Hash::make($validatedData['new_password']);
            $user->save();

            return redirect()->route('user.profile')
                ->with('success', 'Пароль успешно изменён!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Не удалось изменить пароль. Пожалуйста, попробуйте снова.'])
                ->withInput();
        }
    }
}
