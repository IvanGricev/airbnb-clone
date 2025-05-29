<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Booking;
use App\Models\LandlordApplication;
use App\Models\Tag;

class PropertyController extends Controller
{
    /**
     * Отображает список объектов недвижимости с фильтрацией, поиском, кэшированием и пагинацией.
     */
    public function index(Request $request)
    {
        $query        = $request->input('query');
        $selectedTags = $request->input('tags', []);
        $minPrice     = $request->input('min_price');
        $maxPrice     = $request->input('max_price');
        $sortOrder    = $request->input('sort_order', 'asc');

        // Получение тегов с кэшированием.
        $tags = Cache::remember('tags', now()->addHours(1), function () {
            return Tag::all()->groupBy('category');
        });

        $cacheKey = 'properties_' . md5(json_encode($request->all()));

        $properties = Cache::remember($cacheKey, now()->addMinutes(1), function () use ($query, $selectedTags, $minPrice, $maxPrice, $sortOrder) {
            $queryBuilder = Property::with(['tags', 'reviews']);

            if ($query) {
                $queryBuilder->where(function ($q) use ($query) {
                    $q->where('title', 'ilike', '%' . $query . '%')
                      ->orWhere('description', 'ilike', '%' . $query . '%')
                      ->orWhere('address', 'ilike', '%' . $query . '%');
                });
            }

            if (!empty($selectedTags)) {
                $queryBuilder->whereHas('tags', function($q) use ($selectedTags) {
                    $q->whereIn('tags.id', $selectedTags);
                });
            }

            if ($minPrice) {
                $queryBuilder->where('price_per_night', '>=', $minPrice);
            }
            if ($maxPrice) {
                $queryBuilder->where('price_per_night', '<=', $maxPrice);
            }

            $queryBuilder->orderBy('price_per_night', $sortOrder);

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
     * Отображает изображение из базы данных
     */
    public function showImage($id)
    {
        $image = PropertyImage::findOrFail($id);
        return response($image->image_data)
            ->header('Content-Type', $image->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $image->original_name . '"')
            ->header('Cache-Control', 'public, max-age=31536000');
    }

    /**
     * Сохраняет новый объект недвижимости с загрузкой изображений в БД
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для создания жилья.');
        }

        $messages = [
            'title.required' => 'Название жилья обязательно для заполнения.',
            'title.max' => 'Название не должно превышать 255 символов.',
            'description.required' => 'Описание жилья обязательно для заполнения.',
            'description.min' => 'Описание должно содержать минимум 50 символов.',
            'address.required' => 'Адрес обязателен для заполнения.',
            'price_per_night.required' => 'Цена за ночь обязательна для заполнения.',
            'price_per_night.numeric' => 'Цена должна быть числом.',
            'price_per_night.min' => 'Цена не может быть отрицательной.',
            'images.max' => 'Можно загрузить не более 12 изображений.',
            'images.*.image' => 'Файл должен быть изображением.',
            'images.*.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif или svg.',
            'images.*.max' => 'Размер изображения не должен превышать 2MB.',
            'tags.*.exists' => 'Выбранный тег не существует.',
        ];

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'address' => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'tags' => 'array|nullable',
            'tags.*' => 'exists:tags,id',
            'images' => 'required|array|max:12',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], $messages);

        try {
            $property = Property::create([
                'user_id' => Auth::id(),
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'address' => $validatedData['address'],
                'price_per_night' => $validatedData['price_per_night'],
                'latitude' => null,
                'longitude' => null,
            ]);

            if ($request->has('tags')) {
                $property->tags()->sync($request->input('tags'));
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $property->images()->create([
                        'image_data' => file_get_contents($image->getRealPath()),
                        'mime_type' => $image->getMimeType(),
                        'original_name' => $image->getClientOriginalName(),
                        'size' => $image->getSize()
                    ]);
                }
            }

            return redirect()->route('properties.show', $property->id)
                           ->with('success', 'Жильё успешно добавлено.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Произошла ошибка при создании жилья. Пожалуйста, попробуйте снова.'])
                        ->withInput();
        }
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
     * Обновление объекта недвижимости с загрузкой изображений в БД
     */
    public function update(Request $request, Property $property)
    {
        if (Auth::user()->role !== 'landlord') {
            return redirect()->route('home')->with('error', 'У вас нет прав для обновления жилья.');
        }
        if (Auth::id() !== $property->user_id) {
            return redirect()->route('home')->with('error', 'Вы можете обновлять только свои объекты.');
        }

        $messages = [
            'title.required' => 'Название жилья обязательно для заполнения.',
            'title.max' => 'Название не должно превышать 255 символов.',
            'description.required' => 'Описание жилья обязательно для заполнения.',
            'description.min' => 'Описание должно содержать минимум 50 символов.',
            'address.required' => 'Адрес обязателен для заполнения.',
            'price_per_night.required' => 'Цена за ночь обязательна для заполнения.',
            'price_per_night.numeric' => 'Цена должна быть числом.',
            'price_per_night.min' => 'Цена не может быть отрицательной.',
            'images.max' => 'Можно загрузить не более 12 изображений.',
            'images.*.image' => 'Файл должен быть изображением.',
            'images.*.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif или svg.',
            'images.*.max' => 'Размер изображения не должен превышать 2MB.',
            'tags.*.exists' => 'Выбранный тег не существует.',
            'delete_images.*.exists' => 'Выбранное изображение не существует.',
        ];

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'address' => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'tags' => 'array|nullable',
            'tags.*' => 'exists:tags,id',
            'images' => 'nullable|array|max:12',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'delete_images' => 'array|nullable',
            'delete_images.*' => 'exists:property_images,id',
        ], $messages);

        try {
            // Обновляем основную информацию о свойстве
            $property->update([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'address' => $validatedData['address'],
                'price_per_night' => $validatedData['price_per_night'],
            ]);

            // Обновляем теги
            if ($request->has('tags')) {
                $property->tags()->sync($request->input('tags'));
            } else {
                $property->tags()->detach();
            }

            // Удаляем выбранные изображения
            if ($request->has('delete_images')) {
                foreach ($request->input('delete_images') as $imageId) {
                    $image = PropertyImage::find($imageId);
                    if ($image && $image->property_id === $property->id) {
                        $image->delete();
                    }
                }
            }

            // Добавляем новые изображения
            if ($request->hasFile('images')) {
                // Проверяем, не превысим ли лимит в 12 изображений
                $currentImageCount = $property->images()->count();
                $newImageCount = count($request->file('images'));
                
                if ($currentImageCount + $newImageCount > 12) {
                    return back()->withErrors(['error' => 'Общее количество изображений не должно превышать 12.'])
                                ->withInput();
                }

                foreach ($request->file('images') as $image) {
                    $property->images()->create([
                        'image_data' => file_get_contents($image->getRealPath()),
                        'mime_type' => $image->getMimeType(),
                        'original_name' => $image->getClientOriginalName(),
                        'size' => $image->getSize()
                    ]);
                }
            }

            // Проверяем, осталось ли хотя бы одно изображение
            if ($property->images()->count() === 0) {
                return back()->withErrors(['error' => 'Должно быть загружено хотя бы одно изображение.'])
                            ->withInput();
            }

            return redirect()->route('properties.show', $property->id)
                           ->with('success', 'Жильё успешно обновлено.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Произошла ошибка при обновлении жилья: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Удаление изображения из базы данных
     */
    public function deleteImage($id)
    {
        $image = PropertyImage::findOrFail($id);
        $property = $image->property;

        // Проверяем права доступа
        if (Auth::id() !== $property->user_id) {
            return redirect()->route('home')->with('error', 'У вас нет прав для удаления этого изображения.');
        }

        $image->delete();
        return back()->with('success', 'Изображение успешно удалено.');
    }

    /**
     * Отображает детальную информацию об объекте недвижимости с изображениями.
     */
    public function show(Property $property)
    {
        // Если необходимо, можно использовать кэширование
        $property->load('images', 'tags', 'reviews');
        return view('properties.show', compact('property'));
    }

    /**
     * Форма подачи заявки на роль арендодателя.
     */
    public function showBecomeLandlordForm()
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Необходимо авторизоваться');
            }
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
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
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
            Log::error('Ошибка обработки заявки арендодателя: ' . $e->getMessage(), [
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
     * Получает недоступные даты для бронирования объекта.
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

    public function destroy(Property $property)
    {
        if (Auth::id() !== $property->user_id) {
            return redirect()->route('home')->with('error', 'У вас нет прав для удаления этого объекта.');
        }

        try {
            // Удаляем все связанные данные
            $property->images()->delete();
            $property->tags()->detach();
            $property->reviews()->delete();
            $property->bookings()->delete();
            $property->delete();

            return redirect()->route('properties.index')
                           ->with('success', 'Объект успешно удален.');
        } catch (\Exception $e) {
            return back()->with('error', 'Произошла ошибка при удалении объекта.');
        }
    }
}