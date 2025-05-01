@extends('layouts.main')

@section('title', 'Профиль пользователя')

@section('content')
<h1>Профиль {{ Auth::user()->name }}</h1>

<!-- Кнопка "Редактировать имя и email" -->
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editNameEmailModal">Редактировать имя и email</button>

<!-- Модальное окно для редактирования имени и email -->
<div class="modal fade" id="editNameEmailModal" tabindex="-1" aria-labelledby="editNameEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editNameEmailModalLabel">Редактировать имя и email</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <form action="{{ route('user.update.name-email') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Имя</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ Auth::user()->name }}" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ Auth::user()->email }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Сохранить изменения</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Кнопка "Изменить пароль" -->
<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editPasswordModal">Изменить пароль</button>

<!-- Модальное окно для изменения пароля -->
<div class="modal fade" id="editPasswordModal" tabindex="-1" aria-labelledby="editPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPasswordModalLabel">Изменить пароль</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <form action="{{ route('user.update.password') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label for="current_password" class="form-label">Текущий пароль</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="new_password" class="form-label">Новый пароль</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="new_password_confirmation" class="form-label">Подтвердите новый пароль</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Сохранить изменения</button>
        </div>
      </form>
    </div>
  </div>
</div>

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
                <a href="{{ route('properties.edit', $item['property']->id) }}" class="btn">Редактировать</a>
            </div>
        </div>
    @endforeach

@endif
@endsection
<!-- Инфогрфика (не работает) -->
<!-- 
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            @if(isset($bookings))
                var userBookings = @json($bookings);

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
            @endif
        });
    </script>

    @if($user->role === 'landlord')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var ownerCalendarEl = document.getElementById('owner-calendar');

                @if(isset($ownerBookings))
                    var ownerBookings = @json($ownerBookings);

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
                @endif
            });
        </script>
    @endif
@endpush -->
