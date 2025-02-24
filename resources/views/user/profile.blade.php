@extends('layouts.main')

@section('title', 'Профиль пользователя')

@section('content')
<h1>Профиль пользователя</h1>

<!-- Календарь бронирований -->
<h2>Мои бронирования</h2>
<!-- <div id="calendar"></div> -->
@if($bookings->isEmpty())
    <p>У вас нет текущих бронирований.</p>
@else
    <ul>
        @foreach($bookings as $booking)
            <li>
                <a href="{{ route('bookings.show', $booking->id) }}">{{ $booking->property->title }}</a>
                ({{ $booking->start_date }} - {{ $booking->end_date }})
            </li>
        @endforeach
    </ul>
@endif

<!-- Избранные объекты -->
<h2>Избранные объекты</h2>
@if($favorites->isEmpty())
    <p>У вас нет избранных объектов.</p>
@else
    <div class="row">
        @foreach($favorites as $favorite)
            <div class="col-md-4">
                <div class="card mb-4">
                    <!-- Изображение объекта -->
                    <div class="card-body">
                        <h5 class="card-title">{{ $favorite->property->title }}</h5>
                        <p>{{ Str::limit($favorite->property->description, 100) }}</p>
                        <a href="{{ route('properties.show', $favorite->property->id) }}" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- Ранее арендованное жильё -->
<h2>Ранее арендованное жильё</h2>
@if($pastBookings->isEmpty())
    <p>У вас нет ранее завершённых аренд.</p>
@else
    <div class="row">
        @foreach($pastBookings as $booking)
            <div class="col-md-4">
                <div class="card mb-4">
                    <!-- Изображение объекта -->
                    <div class="card-body">
                        <h5 class="card-title">{{ $booking->property->title }}</h5>
                        <p>{{ Str::limit($booking->property->description, 100) }}</p>
                        <a href="{{ route('properties.show', $booking->property->id) }}" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <a href="{{ route('bookings.history') }}" class="btn btn-link">Посмотреть все бронирования</a>
@endif

<!-- Часть арендодателя -->
@if($user->role === 'landlord')
    <h2>Статистика арендодателя</h2>
    <p><strong>Общее количество бронирований:</strong> {{ $totalBookings }}</p>
    <p><strong>Общий доход:</strong> {{ $totalRevenue }} руб.</p>

    <!-- <h2>Календарь бронирований ваших объектов</h2>
    <div id="owner-calendar"></div> -->

    <h2>Мои объекты</h2>
    @foreach($propertyBookings as $item)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">{{ $item['property']->title }}</h5>
                <p><strong>Количество бронирований:</strong> {{ $item['booking_count'] }}</p>
                <p><strong>Доход:</strong> {{ $item['revenue'] }} руб.</p>
                <a href="{{ route('properties.show', $item['property']->id) }}" class="btn btn-primary">Подробнее</a>
            </div>
        </div>
    @endforeach

@endif
@endsection

<!-- 
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            if(isset($bookings))
                var userBookings = json($bookings);

                var colors = ['#FF5733', '#33FF57', '#3357FF', '#F333FF', '#FF33A8']; 

                console.log('User Bookings:', userBookings);

                var events = userBookings.map(function(booking, index) {
                    return {
                        title: booking.property.title,
                        start: booking.start_date,
                        end: booking.end_date,
                        url: '{{ url('/bookings') }}/' + booking.id,
                        color: colors[index % colors.length]
                    };
                });

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: events,
                    eventClick: function(info) {
                        // info.jsEvent.preventDefault();
                    }
                });

                calendar.render();
            endif
        });
    </script>

    @if($user->role === 'landlord')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var ownerCalendarEl = document.getElementById('owner-calendar');

                if(isset($ownerBookings))
                    var ownerBookings = json($ownerBookings);

                    console.log('Owner Bookings:', ownerBookings);

                    var events = ownerBookings.map(function(booking) {
                        return {
                            title: booking.property.title + ' (Забронировано)',
                            start: booking.start_date,
                            end: booking.end_date,
                            url: '{{ url('/bookings') }}/' + booking.id,
                            color: '#F39C12'
                        };
                    });

                    var ownerCalendar = new FullCalendar.Calendar(ownerCalendarEl, {
                        initialView: 'dayGridMonth',
                        events: events,
                        eventClick: function(info) {
                            // info.jsEvent.preventDefault();
                        }
                    });

                    ownerCalendar.render();
                endif
            });
        </script>
    @endif
@endpush -->