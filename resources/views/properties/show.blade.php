@extends('layouts.main')

@section('title', $property->title)

@section('content')
<h1>{{ $property->title }}</h1>

<p>{{ $property->description }}</p>

<p><strong>Адрес:</strong> {{ $property->address }}</p>

<p><strong>Цена за ночь:</strong> {{ $property->price_per_night }} руб.</p>

<!-- Карта с использованием Google Maps -->
@if($property->latitude && $property->longitude)
    <div id="map" style="height: 400px; width: 100%;"></div>

    <script>
    function initMap() {
        var location = {
            lat: "{{ $property->latitude }}",
            lng: "{{ $property->longitude }}"
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
    <!--6fd5f83864f728e АААААААААААААААААААААААААААА нет ключа-->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA_tKbW6A5pQ-eupxI56myUnHLqYCzOjKo&libraries=places&callback=initMap"></script>
@endif

<!-- Форма бронирования -->
@auth
    <h2>Бронирование</h2>
    <form action="{{ route('bookings.store') }}" method="POST">
        @csrf
        <input type="hidden" name="property_id" value="{{ $property->id }}">
        <div class="mb-3">
            <label for="start_date" class="form-label">Дата заезда</label>
            <input type="date" name="start_date" class="form-control">
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Дата выезда</label>
            <input type="date" name="end_date" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Забронировать</button>
    </form>
@endauth

@endsection