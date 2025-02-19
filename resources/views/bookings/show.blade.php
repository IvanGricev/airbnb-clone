@extends('layouts.main')

@section('title', 'Мои бронирования')

@section('content')
<h1>Мои бронирования</h1>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($bookings->isEmpty())
    <p>У вас нет бронирований.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Жильё</th>
                <th>Дата заезда</th>
                <th>Дата выезда</th>
                <th>Общая стоимость</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->property->title }}</td>
                <td>{{ $booking->start_date }}</td>
                <td>{{ $booking->end_date }}</td>
                <td>{{ $booking->total_price }} руб.</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
