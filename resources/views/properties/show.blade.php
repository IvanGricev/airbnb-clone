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
    <p><strong>Средний рейтинг:</strong>
        @php
            $averageRating = round($property->reviews->avg('rating'), 1);
        @endphp
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

@auth
    @php
        $isFavorite = \App\Models\Favorite::where('user_id', Auth::id())
            ->where('property_id', $property->id)
            ->exists();
    @endphp

    <form action="{{ $isFavorite ? route('favorites.remove', $property->id) : route('favorites.add', $property->id) }}" method="POST" style="display:inline-block;">
        @csrf
        <button type="submit" class="btn btn-{{ $isFavorite ? 'danger' : 'success' }}">
            {{ $isFavorite ? 'Убрать из избранного' : 'В избранное' }}
        </button>
    </form>
@endauth

@auth
    @php
        $userId = Auth::id();
        $hasCompletedBooking = \App\Models\Booking::where('property_id', $property->id)
            ->where('user_id', $userId)
            ->where('end_date', '<', now())
            ->where('status', 'confirmed')
            ->exists();

        $alreadyReviewed = \App\Models\Review::where('property_id', $property->id)
            ->where('user_id', $userId)
            ->exists();
    @endphp

    @if($hasCompletedBooking && !$alreadyReviewed)
        <a href="{{ route('reviews.create', $property->id) }}" class="btn btn-secondary">Оставить отзыв</a>
    @endif
@endauth

<!-- Карта с использованием Google Maps -->
@if($property->latitude && $property->longitude)
    <div id="map" style="height: 400px; width: 100%;"></div>

    <script>
    function initMap() {
        var location = {
            lat: @json($property->latitude),
            lng: @json($property->longitude)
        };

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: location
        });
        var marker = new google.maps.Marker({
            position: location,
            map: map
        });
    }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>
@endif

<!-- Форма бронирования -->
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
            <input type="text" name="start_date" id="start_date" class="form-control" autocomplete="off" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Дата выезда</label>
            <input type="text" name="end_date" id="end_date" class="form-control" autocomplete="off" required>
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

            // Function to disable unavailable dates
            function disableDates(date) {
                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                return [ unavailableDates.indexOf(string) == -1 ];
            }

            // Fetch unavailable dates via AJAX
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

@endsection
