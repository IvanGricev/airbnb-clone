@extends('layouts.main')

@section('title', 'Мои бронирования')

@section('content')
<h1>Мои бронирования</h1>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
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
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->property->title }}</td>
                <td>{{ $booking->start_date }}</td>
                <td>{{ $booking->end_date }}</td>
                <td>{{ $booking->total_price }} руб.</td>
                <td>
                    @if($booking->status == 'confirmed')
                        Подтверждено
                    @elseif($booking->status == 'cancelled_by_user')
                        Отменено вами
                    @elseif($booking->status == 'cancelled_by_landlord')
                        Отменено арендодателем
                    @endif
                </td>
                <td>
                    <a href="{{ route('chat.index', ['withUserId' => $booking->property->user_id]) }}" class="btn btn-info">Чат с арендодателем</a>
                    @if($booking->status == 'confirmed' && $booking->canBeCancelled())
                        <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" style="display:inline-block;">
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

    <!-- Пагинация -->
    {{ $bookings->links() }}
@endif
@endsection
