<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FavoriteController extends Controller
{
    public function add($propertyId)
    {
        try {
            // Проверка, что propertyId является положительным числом
            if (!is_numeric($propertyId) || $propertyId <= 0) {
                return redirect()->back()->with('error', 'Некорректный идентификатор жилья.');
            }

            $userId = Auth::id();

            // Проверяем, существует ли уже запись
            $exists = Favorite::where([
                'user_id' => $userId,
                'property_id' => $propertyId,
            ])->exists();

            if (!$exists) {
                // Создаем запись в избранном
                Favorite::create([
                    'user_id' => $userId,
                    'property_id' => $propertyId,
                ]);

                // Очищаем кэш избранных объектов пользователя
                Cache::forget('user_favorites_' . $userId);

                return redirect()->back()->with('success', 'Жильё добавлено в избранное.');
            }

            return redirect()->back()->with('info', 'Жильё уже в избранном.');
        } catch (\Exception $e) {
            Log::error("Ошибка при добавлении в избранное: " . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при добавлении в избранное.');
        }
    }

    public function remove($propertyId)
    {
        try {
            // Проверка, что propertyId является положительным числом
            if (!is_numeric($propertyId) || $propertyId <= 0) {
                return redirect()->back()->with('error', 'Некорректный идентификатор жилья.');
            }

            $userId = Auth::id();

            // Проверяем, существует ли запись
            $favorite = Favorite::where([
                'user_id' => $userId,
                'property_id' => $propertyId,
            ])->first();

            if ($favorite) {
                // Удаляем запись из избранного
                $favorite->delete();

                // Очищаем кэш избранных объектов пользователя
                Cache::forget('user_favorites_' . $userId);

                return redirect()->back()->with('success', 'Жильё удалено из избранного.');
            }

            return redirect()->back()->with('info', 'Жильё не было в избранном.');
        } catch (\Exception $e) {
            Log::error("Ошибка при удалении из избранного: " . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при удалении из избранного.');
        }
    }
}