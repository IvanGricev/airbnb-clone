@extends('layouts.main')

@section('title', $property->title)

@section('content')
<h1>{{ $property->title }}</h1>

<p>{{ $property->description }}</p>

<p><strong>Адрес:</strong> {{ $property->address }}</p>

<p><strong>Цена за ночь:</strong> {{ $property->price_per_night }} руб.</p>

<!--
Карта с использованием Google Maps
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
-->

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
