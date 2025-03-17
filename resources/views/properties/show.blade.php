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

<!-- Остальной контент объекта (например, форма бронирования) -->
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

@endsection
