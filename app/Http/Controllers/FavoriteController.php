<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

            // Создаем запись в избранном, если такой еще нет
            Favorite::firstOrCreate([
                'user_id'     => $userId,
                'property_id' => $propertyId,
            ]);

            return redirect()->back()->with('success', 'Жильё добавлено в избранное.');
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

            // Удаление записи из избранного
            Favorite::where([
                'user_id'     => $userId,
                'property_id' => $propertyId,
            ])->delete();

            return redirect()->back()->with('success', 'Жильё удалено из избранного.');
        } catch (\Exception $e) {
            Log::error("Ошибка при удалении из избранного: " . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при удалении из избранного.');
        }
    }
}