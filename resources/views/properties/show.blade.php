@extends('layouts.main')

@section('title', $property->title)

@section('content')
    <h1>{{ $property->title }}</h1>

    @auth
        @if(Auth::id() == $property->user_id)
            <a href="{{ route('properties.edit', $property->id) }}" class="btn btn-warning">Редактировать</a>
        @endif
    @endauth

    @if($property->reviews->count() > 0)
        <p>
            <strong>Средний рейтинг:</strong>
            @php $averageRating = round($property->reviews->avg('rating'), 1); @endphp
            {{ $averageRating }} из 5
        </p>
    @endif

    <p>{{ $property->description }}</p>
    <p><strong>Адрес:</strong> {{ $property->address }}</p>
    <p><strong>Цена за ночь:</strong> {{ $property->price_per_night }} руб.</p>

    @if($property->tags->isNotEmpty())
        <p><strong>Теги:</strong>
            @foreach($property->tags as $tag)
                <span class="badge bg-secondary">{{ $tag->name }}</span>
            @endforeach
        </p>
    @endif

    <!-- Карусель изображений -->
    @if($property->images->count() > 1)
        <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach($property->images as $index => $image)
                    <div class="carousel-item @if($index == 0) active @endif">
                        <img src="{{ asset('storage/' . $image->image_path) }}" class="d-block w-100" alt="Изображение жилья">
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Предыдущий</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Следующий</span>
            </button>
        </div>
    @elseif($property->images->count() == 1)
        <img src="{{ asset('storage/' . $property->images->first()->image_path) }}" class="img-fluid" alt="Изображение жилья">
    @else
        <img src="{{ asset('storage/default-placeholder.png') }}" class="img-fluid" alt="Нет изображения">
    @endif

    <!-- Вывод отзывов -->
    <hr>
    <h2>Отзывы</h2>
    @if($property->reviews->count() > 0)
        @foreach($property->reviews as $review)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">
                        {{ $review->user->name }}
                        <small class="text-muted">{{ $review->created_at->format('d.m.Y H:i') }}</small>
                    </h5>
                    <p class="card-text">
                        <strong>Оценка:</strong> {{ $review->rating }} из 5
                    </p>
                    @if($review->comment)
                        <p class="card-text">{{ $review->comment }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <p>Этот объект пока не имеет отзывов.</p>
    @endif

    <!-- Форма бронирования объекта -->
    @auth
        <h2>Бронирование</h2>
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('bookings.store') }}" method="POST">
            @csrf
            <input type="hidden" name="property_id" value="{{ $property->id }}">
            <div class="mb-3">
                <label for="start_date" class="form-label">Дата заезда</label>
                <input type="date" name="start_date" id="start_date" class="form-control" autocomplete="off" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">Дата выезда</label>
                <input type="date" name="end_date" id="end_date" class="form-control" autocomplete="off" required>
            </div>
            <button type="submit" class="btn btn-success">Забронировать</button>
        </form>

        <!-- Подключение Datepicker -->
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <script>
            $(function() {
                var propertyId = @json($property->id);
                var unavailableDates = [];
                function disableDates(date) {
                    var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    return [ unavailableDates.indexOf(string) == -1 ];
                }
                $.ajax({
                    url: '/properties/' + propertyId + '/unavailable-dates',
                    method: 'GET',
                    success: function(dates) {
                        unavailableDates = dates;
                        $('#start_date, #end_date').datepicker({
                            dateFormat: 'yy-mm-dd',
                            minDate: 0,
                            beforeShowDay: disableDates
                        });
                    }
                });
            });
        </script>
    @endauth

    <!-- Кнопка добавления/удаления из избранного -->
    @auth
        @php
            $favorites = Auth::user()->favorites;
            $isFavorite = $favorites ? $favorites->contains('property_id', $property->id) : false;
        @endphp

        @if ($isFavorite)
            <form action="{{ route('favorites.remove', $property->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Удалить из избранного</button>
            </form>
        @else
            <form action="{{ route('favorites.add', $property->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-info">Добавить в избранное</button>
            </form>
        @endif
    @endauth

    <!-- Форма оставления отзыва (отображается только если бронирование завершено) -->
    @auth
        @php
            $hasCompletedBooking = \App\Models\Booking::where('property_id', $property->id)
                ->where('user_id', Auth::id())
                ->where('end_date', '<', now())
                ->where('status', 'confirmed')
                ->exists();
        @endphp

        @if($hasCompletedBooking)
            <h2>Оставить отзыв</h2>
            @if(session('error_review'))
                <div class="alert alert-danger">{{ session('error_review') }}</div>
            @endif
            @if(session('success_review'))
                <div class="alert alert-success">{{ session('success_review') }}</div>
            @endif
            <a href="{{ route('reviews.create', $property->id) }}" class="btn btn-secondary">Добавить отзыв</a>
        @endif
    @endauth

@endsection