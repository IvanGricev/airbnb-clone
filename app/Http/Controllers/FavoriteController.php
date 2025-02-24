<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function add($propertyId)
    {
        $userId = Auth::id();

        Favorite::firstOrCreate([
            'user_id' => $userId,
            'property_id' => $propertyId,
        ]);

        return redirect()->back()->with('success', 'Жильё добавлено в избранное.');
    }

    public function remove($propertyId)
    {
        $userId = Auth::id();

        Favorite::where([
            'user_id' => $userId,
            'property_id' => $propertyId,
        ])->delete();

        return redirect()->back()->with('success', 'Жильё удалено из избранного.');
    }
}
