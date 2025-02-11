<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PropertyController extends Controller
{
    // Отображение списка жилья
    public function index()
    {
        $properties = Property::all();
        return view('properties.index', compact('properties'));
    }

    // Форма создания жилья (для арендодателей)
    public function create()
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для создания жилья.');
        }
        return view('properties.create');
    }

    // Сохранение нового жилья
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для создания жилья.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'price_per_night' => 'required|numeric',
        ]);

        $property = new Property();
        $property->user_id = Auth::id();
        $property->title = $request->title;
        $property->description = $request->description;
        $property->address = $request->address;
        $property->price_per_night = $request->price_per_night;

        // Здесь можно добавить логику для получения координат по адресу
        $property->latitude = null; // placeholder
        $property->longitude = null; // placeholder

        $property->save();

        return redirect()->route('properties.show', $property)->with('success', 'Жильё успешно добавлено.');
    }

    // Отображение конкретного жилья
    public function show(Property $property)
    {
        return view('properties.show', compact('property'));
    }

    public function showBecomeLandlordForm()
    {
        return view('properties.become_landlord');
    }

    public function storeAsLandlord(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'address'         => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
        ]);

        // Обновление роли пользователя на 'landlord'
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role !== 'landlord') {
            $user->role = 'landlord';
            $user->save();
        }

        // Создание нового объекта жилья
        $property = new Property();
        $property->user_id         = $user->id;
        $property->title           = $request->title;
        $property->description     = $request->description;
        $property->address         = $request->address;
        $property->price_per_night = $request->price_per_night;

        // Здесь можно добавить логику геокодирования адреса для получения координат
        $property->latitude  = null; // Заглушка
        $property->longitude = null; // Заглушка

        $property->save();

        return redirect()->route('properties.show', $property)->with('success', 'Вы стали арендодателем, и ваше жильё добавлено.');
    }
    
}
