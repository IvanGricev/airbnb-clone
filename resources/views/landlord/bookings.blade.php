@extends('layouts.main')

@section('title', 'Бронирования моих объектов')

@section('content')
<h1>Бронирования моих объектов</h1>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($bookings->isEmpty())
    <p>На ваши объекты нет бронирований.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Жильё</th>
                <th>Арендатор</th>
                <th>Дата заезда</th>
                <th>Дата выезда</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->property->title }}</td>
                <td>{{ $booking->user->name }}</td>
                <td>{{ $booking->start_date }}</td>
                <td>{{ $booking->end_date }}</td>
                <td>
                    @if($booking->status == 'confirmed')
                        Подтверждено
                    @elseif($booking->status == 'cancelled_by_user')
                        Отменено арендатором
                    @elseif($booking->status == 'cancelled_by_landlord')
                        Отменено вами
                    @endif
                </td>
                <td>
                    @if($booking->status == 'confirmed' && $booking->canBeCancelled())
                        <form action="{{ route('bookings.cancelByLandlord', $booking->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <button type="submit" class="btn btn-danger">Отменить</button>
                        </form>
                    @else
                        Нет доступных действий
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
