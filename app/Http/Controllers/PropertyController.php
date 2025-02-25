<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\LandlordApplication;
use App\Models\User;
use App\Models\Tag;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
        $selectedTags = $request->input('tags', []);

        // Получаем все категории тегов и их соответствующие теги
        $tags = Tag::all()->groupBy('category');

        $properties = Property::with('tags');

        // Поиск по заголовку и описанию
        if ($query) {
            $properties->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->orWhere('address', 'like', '%' . $query . '%');
            });
        }

        // Фильтрация по выбранным тегам
        if (!empty($selectedTags)) {
            $properties->whereHas('tags', function($q) use ($selectedTags) {
                $q->whereIn('tags.id', $selectedTags);
            });
        }

        $properties = $properties->get();

        return view('properties.index', compact('properties', 'query', 'selectedTags', 'tags'));
    }
        

    // Форма создания жилья (для арендодателей)
    public function create()
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для создания жилья.');
        }
    
        $tags = Tag::all()->groupBy('category');
    
        return view('properties.create', compact('tags'));
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
            'tags' => 'array|nullable',
            'tags.*' => 'exists:tags,id',
        ]);

        $property = new Property();
        $property->user_id = Auth::id();
        $property->title = $request->title;
        $property->description = $request->description;
        $property->address = $request->address;
        $property->price_per_night = $request->price_per_night;
        $property->latitude = null;
        $property->longitude = null;
        $property->save();

        if ($request->has('tags')) {
            $property->tags()->sync($request->input('tags'));
        }

        return redirect()->route('properties.show', $property)->with('success', 'Жильё успешно добавлено.');
    }

    public function update(Request $request, Property $property)
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для обновления жилья.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'price_per_night' => 'required|numeric',
            'tags' => 'nullable|string',
        ]);

        $property->title = $request->title;
        $property->description = $request->description;
        $property->address = $request->address;
        $property->price_per_night = $request->price_per_night;
        $property->save();

        if ($request->has('tags')) {
            $property->tags()->sync($request->input('tags'));
        } else {
            $property->tags()->detach();
        }    

        return redirect()->route('properties.show', $property)->with('success', 'Жильё успешно обновлено.');
    }


    // Отображение конкретного жилья
    public function show(Property $property)
    {
        return view('properties.show', compact('property'));
    }

    // Форма подачи заявки на роль арендодателя
    public function showBecomeLandlordForm()
    {
        try {
            // Убедитесь, что пользователь авторизован
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Необходимо авторизоваться');
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user) {
                Log::error('Пользователь не найден после авторизации', [
                    'auth_id' => Auth::id(),
                    'session_data' => session()->all(),
                ]);
                return redirect()->route('login')->with('error', 'Произошла ошибка при загрузке данных пользователя');
            }

            return view('properties.become_landlord', compact('user'));
        } catch (\Exception $e) {
            Log::error('Ошибка при отображении формы подачи заявки арендодателя', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке формы');
        }
    }

    // Сохранение заявки на роль арендодателя
    public function storeAsLandlord(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:50',
                'middle_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'passport_number' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^\d{4} \d{6}$/'
                ],
                'passport_expiration_month' => 'required|integer|between:1,12',
                'passport_expiration_year' => 'required|integer|min:' . date('y'), // Используем двухзначный год
            ], [
                'first_name.required' => 'Поле "Имя" обязательно для заполнения.',
                'middle_name.required' => 'Поле "Отчество" обязательно для заполнения.',
                'last_name.required' => 'Поле "Фамилия" обязательно для заполнения.',
                'passport_number.required' => 'Поле "Номер паспорта" обязательно для заполнения.',
                'passport_number.regex' => 'Неверный формат паспорта. Используйте формат: XXXX XXXXXX.',
                'passport_expiration_month.required' => 'Поле "Месяц действия паспорта" обязательно для заполнения.',
                'passport_expiration_year.required' => 'Поле "Год действия паспорта" обязательно для заполнения.',
                'passport_expiration_year.min' => 'Год действия паспорта должен быть не меньше текущего.',
            ]);

            $user = Auth::user();

            if (LandlordApplication::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists()) {
                return redirect()->back()
                    ->with('error', 'Вы уже подали заявку или она уже была одобрена.')
                    ->withInput();
            }

            // Формируем дату в формате mm/yy
            $expirationDate = sprintf('%02d/%02d',
                $validated['passport_expiration_month'],
                $validated['passport_expiration_year']
            );

            $user->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'passport_number' => $validated['passport_number'],
                'passport_expiration_date' => $expirationDate, // Сохраняем в формате mm/yy
            ]);

            LandlordApplication::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'message' => 'Заявка на роль арендодателя', // Предустановленное сообщение
            ]);

            return redirect()->route('home')
                ->with('success', 'Ваша заявка успешно подана на рассмотрение.');

        } catch (\Exception $e) {
            Log::error('Ошибка обработки заявки арендодателя: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'input' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Произошла ошибка при обработке вашей заявки: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function getUnavailableDates($propertyId)
    {
        $bookings = Booking::where('property_id', $propertyId)
            ->where('status', 'confirmed')
            ->get(['start_date', 'end_date']);

        $unavailableDates = [];

        foreach ($bookings as $booking) {
            $start = new \DateTime($booking->start_date);
            $end = new \DateTime($booking->end_date);
            $end = $end->modify('+1 day');

            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, $end);

            foreach ($period as $date) {
                $unavailableDates[] = $date->format('Y-m-d');
            }
        }

        return response()->json($unavailableDates);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $tagFilter = $request->input('tags');

        $properties = Property::with('tags')
            ->where('title', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%');

        if ($tagFilter) {
            $tags = explode(',', $tagFilter);
            $properties->whereHas('tags', function($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        $properties = $properties->get();

        return view('properties.index', compact('properties', 'query', 'tagFilter'));
    }

}
