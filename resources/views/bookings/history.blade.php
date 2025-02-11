@extends('layouts.main')

@section('title', 'Мои бронирования')

@section('content')
<h1>Мои бронирования</h1>

@if($bookings->isEmpty())
    <p>У вас пока нет бронирований.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Жильё</th>
                <th>Даты проживания</th>
                <th>Общая стоимость</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td><a href="{{ route('properties.show', $booking->property) }}">{{ $booking->property->title }}</a></td>
                <td>{{ $booking->start_date }} - {{ $booking->end_date }}</td>
                <td>{{ $booking->total_price }} руб.</td>
                <td>Подтверждено</td> <!-- Можно добавить статус бронирования -->
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
