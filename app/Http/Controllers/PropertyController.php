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
use Illuminate\Support\Facades\Cache;

class PropertyController extends Controller
{
    /**
     * Отображает список объектов недвижимости с поиском, фильтрацией и сортировкой.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query         = $request->input('query');
        $selectedTags  = $request->input('tags', []);
        $minPrice      = $request->input('min_price');
        $maxPrice      = $request->input('max_price');
        $sortOrder     = $request->input('sort_order', 'asc');

        // Получение тегов с использованием кэширования.
        $tags = Cache::remember('tags', now()->addHours(1), function () {
            return Tag::all()->groupBy('category');
        });

        // Формирование ключа для кэширования результатов поиска.
        $cacheKey = 'properties_' . md5(json_encode($request->all()));
        // Получаем список объектов с eager loading зависимостей
        $properties = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query, $selectedTags, $minPrice, $maxPrice, $sortOrder) {
            $queryBuilder = Property::with(['tags', 'reviews']);
            
            // Поиск по заголовку, описанию и адресу
            if ($query) {
                $queryBuilder->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                      ->orWhere('description', 'like', '%' . $query . '%')
                      ->orWhere('address', 'like', '%' . $query . '%');
                });
            }

            // Фильтрация по выбранным тегам
            if (!empty($selectedTags)) {
                $queryBuilder->whereHas('tags', function($q) use ($selectedTags) {
                    $q->whereIn('tags.id', $selectedTags);
                });
            }

            // Фильтрация по цене
            if ($minPrice) {
                $queryBuilder->where('price_per_night', '>=', $minPrice);
            }
            if ($maxPrice) {
                $queryBuilder->where('price_per_night', '<=', $maxPrice);
            }

            // Сортировка по цене
            $queryBuilder->orderBy('price_per_night', $sortOrder);

            // Используем пагинацию, если объектов много, иначе можно использовать ->get()
            return $queryBuilder->paginate(10);
        });

        return view('properties.index', compact('properties', 'query', 'selectedTags', 'tags', 'minPrice', 'maxPrice', 'sortOrder'));
    }

    /**
     * Форма создания объекта недвижимости (для арендодателей).
     */
    public function create()
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для создания жилья.');
        }
    
        $tags = Tag::all()->groupBy('category');
    
        return view('properties.create', compact('tags'));
    }
    
    /**
     * Сохранение нового объекта недвижимости.
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для создания жилья.');
        }

        $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'address'         => 'required|string',
            'price_per_night' => 'required|numeric',
            'tags'            => 'array|nullable',
            'tags.*'          => 'exists:tags,id',
        ]);

        $property = new Property();
        $property->user_id = Auth::id();
        $property->title = $request->title;
        $property->description = $request->description;
        $property->address = $request->address;
        $property->price_per_night = $request->price_per_night;
        $property->latitude = null; // или задайте реальные значения, если доступны
        $property->longitude = null;
        $property->save();

        if ($request->has('tags')) {
            $property->tags()->sync($request->input('tags'));
        }

        return redirect()->route('properties.show', $property)->with('success', 'Жильё успешно добавлено.');
    }

    /**
     * Форма редактирования объекта недвижимости.
     */
    public function edit(Property $property)
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для редактирования жилья.');
        }
        if (Auth::id() !== $property->user_id) {
            return redirect()->route('home')->with('error', 'Вы можете редактировать только свои объекты.');
        }

        $tags = Tag::all()->groupBy('category');
        return view('properties.edit', compact('property', 'tags'));
    }

    /**
     * Обновление объекта недвижимости.
     */
    public function update(Request $request, Property $property)
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для обновления жилья.');
        }
        if (Auth::id() !== $property->user_id) {
            return redirect()->route('home')->with('error', 'Вы можете обновлять только свои объекты.');
        }

        $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'address'         => 'required|string',
            'price_per_night' => 'required|numeric',
            'tags'            => 'array|nullable',
            'tags.*'          => 'exists:tags,id',
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

    /**
     * Отображает конкретный объект недвижимости.
     */
    public function show(Property $property)
    {
        return view('properties.show', compact('property'));
    }

    /**
     * Отображает форму подачи заявки на роль арендодателя.
     */
    public function showBecomeLandlordForm()
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Необходимо авторизоваться');
            }
            $user = Auth::user();
            if (!$user) {
                \Log::error('Пользователь не найден после авторизации', [
                    'auth_id' => Auth::id(),
                    'session_data' => session()->all(),
                ]);
                return redirect()->route('login')->with('error', 'Произошла ошибка при загрузке данных пользователя');
            }

            return view('properties.become_landlord', compact('user'));
        } catch (\Exception $e) {
            \Log::error('Ошибка при отображении формы подачи заявки арендодателя', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке формы');
        }
    }

    /**
     * Сохраняет заявку на роль арендодателя.
     */
    public function storeAsLandlord(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name'                => 'required|string|max:50',
                'middle_name'               => 'required|string|max:50',
                'last_name'                 => 'required|string|max:50',
                'passport_number'           => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^\d{4} \d{6}$/'
                ],
                'passport_expiration_month' => 'required|integer|between:1,12',
                'passport_expiration_year'  => 'required|integer|min:' . date('y'),
            ], [
                'first_name.required'                => 'Поле "Имя" обязательно для заполнения.',
                'middle_name.required'               => 'Поле "Отчество" обязательно для заполнения.',
                'last_name.required'                 => 'Поле "Фамилия" обязательно для заполнения.',
                'passport_number.required'           => 'Поле "Номер паспорта" обязательно для заполнения.',
                'passport_number.regex'              => 'Неверный формат паспорта. Используйте формат: XXXX XXXXXX.',
                'passport_expiration_month.required' => 'Поле "Месяц действия паспорта" обязательно для заполнения.',
                'passport_expiration_year.required'  => 'Поле "Год действия паспорта" обязательно для заполнения.',
                'passport_expiration_year.min'       => 'Год действия паспорта должен быть не меньше текущего.',
            ]);

            $user = Auth::user();

            if (\App\Models\LandlordApplication::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists()) {
                return redirect()->back()
                    ->with('error', 'Вы уже подали заявку или она уже была одобрена.')
                    ->withInput();
            }

            $expirationDate = sprintf('%02d/%02d',
                $validated['passport_expiration_month'],
                $validated['passport_expiration_year']
            );

            $user->update([
                'first_name'               => $validated['first_name'],
                'middle_name'              => $validated['middle_name'],
                'last_name'                => $validated['last_name'],
                'passport_number'          => $validated['passport_number'],
                'passport_expiration_date' => $expirationDate,
            ]);

            \App\Models\LandlordApplication::create([
                'user_id' => $user->id,
                'status'  => 'pending',
                'message' => 'Заявка на роль арендодателя',
            ]);

            return redirect()->route('home')->with('success', 'Ваша заявка успешно подана на рассмотрение.');
        } catch (\Exception $e) {
            \Log::error('Ошибка обработки заявки арендодателя: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'input'     => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Произошла ошибка при обработке вашей заявки: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Получение недоступных дат для бронирования объекта.
     */
    public function getUnavailableDates($propertyId)
    {
        $bookings = Booking::where('property_id', $propertyId)
            ->where('status', 'confirmed')
            ->get(['start_date', 'end_date']);

        $unavailableDates = [];
        foreach ($bookings as $booking) {
            $start = new \DateTime($booking->start_date);
            $end   = new \DateTime($booking->end_date);
            $end   = $end->modify('+1 day');

            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, $end);
            foreach ($period as $date) {
                $unavailableDates[] = $date->format('Y-m-d');
            }
        }

        return response()->json($unavailableDates);
    }
}