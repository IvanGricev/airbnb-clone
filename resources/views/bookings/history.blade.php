@extends('layouts.main')

@section('title', 'Мои бронирования')

@section('content')
    <link rel="stylesheet" href="{{ url('/css/historyblade.css') }}">
    <div class="booking-history">
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
            <div class="no-bookings">
                <p>У вас пока нет бронирований.</p>
            </div>
        @else
            <div class="booking-cards">
                @foreach($bookings as $booking)
                    <div class="booking-card">
                        <a href="{{ route('properties.show', $booking->property->id) }}">
                            <div class="booking-card-image">
                                @if($booking->property->images->count() > 0)
                                    <img src="{{ asset('storage/' . $booking->property->images->first()->image_path) }}" 
                                         alt="{{ $booking->property->title }}" loading="lazy">
                                @else
                                    <img src="{{ asset('storage/default-placeholder.png') }}" 
                                         alt="Нет изображения" loading="lazy">
                                @endif
                            </div>
                        </a>
                        <div class="booking-card-header">
                            <h3>
                                <a href="{{ route('properties.show', $booking->property->id) }}">
                                    {{ $booking->property->title }}
                                </a>
                            </h3>
                        </div>
                        <div class="booking-card-body">
                            <div class="booking-info">
                                <p>
                                    <i class="fas fa-calendar-check"></i>
                                    Заезд: {{ \Carbon\Carbon::parse($booking->start_date)->format('d.m.Y') }}
                                </p>
                                <p>
                                    <i class="fas fa-calendar-times"></i>
                                    Выезд: {{ \Carbon\Carbon::parse($booking->end_date)->format('d.m.Y') }}
                                </p>
                                <p class="booking-price">
                                    <i class="fas fa-ruble-sign"></i>
                                    {{ number_format($booking->total_price, 2, ',', ' ') }} руб.
                                </p>
                            </div>

                            <div class="booking-status 
                                @if($booking->status === 'confirmed')
                                    status-confirmed
                                @elseif($booking->status === 'cancelled_by_user' || $booking->status === 'cancelled_by_landlord')
                                    status-cancelled
                                @else
                                    status-pending
                                @endif">
                                @if($booking->status === 'confirmed')
                                    Подтверждено
                                @elseif($booking->status === 'cancelled_by_user')
                                    Отменено вами
                                @elseif($booking->status === 'cancelled_by_landlord')
                                    Отменено арендодателем
                                @elseif($booking->status === 'pending_payment')
                                    <a href="{{ route('payments.checkout', $booking->id) }}" class="status-link">В ожидании оплаты</a>
                                @else
                                    {{ $booking->status }}
                                @endif
                            </div>

                            <div class="booking-actions">
                                <a href="{{ route('chat.index', ['withUserId' => $booking->property->user_id]) }}" class="btn btn-info">
                                    <i class="fas fa-comments"></i> Чат
                                </a>
                                @if($booking->status === 'confirmed' && method_exists($booking, 'canBeCancelled') && $booking->canBeCancelled())
                                    <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Отменить
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Пагинация -->
            @if(method_exists($bookings, 'links'))
                <div class="pagination">
                    {{ $bookings->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection