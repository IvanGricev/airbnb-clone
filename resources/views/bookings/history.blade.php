@extends('layouts.main')

@section('title', 'Мои бронирования')

@section('content')
    <h1>Мои бронирования</h1>

    <!-- Уведомления -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Проверка наличия бронирований -->
    @if($bookings->isEmpty())
        <p>У вас пока нет бронирований.</p>
    @else
        <table class="table table-striped">
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
                        <td>
                            <a href="{{ route('properties.show', $booking->property->id) }}">
                                {{ $booking->property->title }}
                            </a>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($booking->start_date)->format('d.m.Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->end_date)->format('d.m.Y') }}</td>
                        <td>{{ number_format($booking->total_price, 2, ',', ' ') }} руб.</td>
                        <td>
                            @if($booking->status === 'confirmed')
                                Подтверждено
                            @elseif($booking->status === 'cancelled_by_user')
                                Отменено вами
                            @elseif($booking->status === 'cancelled_by_landlord')
                                Отменено арендодателем
                            @else
                                В ожидании оплаты
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('chat.index', ['withUserId' => $booking->property->user_id]) }}" class="btn btn-info btn-sm">Чат с арендодателем</a>
                            @if($booking->status === 'confirmed' && method_exists($booking, 'canBeCancelled') && $booking->canBeCancelled())
                                <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Отменить</button>
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
        @if(method_exists($bookings, 'links'))
            <div class="d-flex justify-content-center">
                {{ $bookings->links() }}
            </div>
        @endif

    @endif
@endsection