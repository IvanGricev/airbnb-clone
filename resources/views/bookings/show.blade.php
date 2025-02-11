@extends('layouts.main')

@section('title', 'Бронирование')

@section('content')
<h1>Информация о бронировании</h1>

<p><strong>Жильё:</strong> <a href="{{ route('properties.show', $booking->property) }}">{{ $booking->property->title }}</a></p>
<p><strong>Даты проживания:</strong> с {{ $booking->start_date }} по {{ $booking->end_date }}</p>
<p><strong>Общая стоимость:</strong> {{ $booking->total_price }} руб.</p>

@if(Auth::id() === $booking->property->user_id)
    <p>Это бронирование вашего жилья.</p>
@endif

@if(Auth::id() === $booking->user_id)
    <p>Вы забронировали это жильё.</p>
@endif

@endsection
