@extends('layouts.main')

@section('title', 'Бронирования моих объектов')

@section('content')
<link rel="stylesheet" href="{{ url('/css/bookingsland.css') }}">
<div class="bookings-landlord-container">
    <div class="bookings-landlord-header">
        <h1>Бронирования моих объектов</h1>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($bookings->isEmpty())
        <p>На ваши объекты нет бронирований.</p>
    @else
        <div class="bookings-landlord-grid">
            @foreach($bookings as $booking)
                <div class="booking-land-card">
                    <img src="{{ $booking->property->images->first() ? asset('storage/' . $booking->property->images->first()->image_path) : asset('images/user-placeholder.svg') }}" alt="{{ $booking->property->title }}" class="booking-land-image">
                    <div class="booking-land-content">{{ $booking->property->title }}</div>
                    <div class="booking-land-details">
                        <div class="booking-land-tenant">Арендатор: <b>{{ $booking->user->name }}</b></div>
                        <div class="booking-land-dates">Заезд: {{ $booking->start_date }}</div>
                        <div class="booking-land-dates">Выезд: {{ $booking->end_date }}</div>
                        <div class="booking-land-status">
                            @if($booking->status == 'confirmed')
                                <span class="booking-land-status-badge">Подтверждено</span>
                            @elseif($booking->status == 'cancelled_by_user')
                                <span class="booking-land-status-badge booking-land-status-cancel">Отменено арендатором</span>
                            @elseif($booking->status == 'cancelled_by_landlord')
                                <span class="booking-land-status-badge booking-land-status-cancel">Отменено вами</span>
                            @else
                                <span class="booking-land-status-badge">{{ $booking->status }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="booking-land-actions">
                        <a href="{{ route('chat.index', ['withUserId' => $booking->user_id]) }}" class="booking-land-btn">Чат с арендатором</a>
                        @if($booking->status == 'confirmed' && $booking->canBeCancelled())
                            <form action="{{ route('bookings.cancelByLandlord', $booking->id) }}" method="POST" style="display:inline-block; width:100%;">
                                @csrf
                                <button type="submit" class="booking-land-btn-cancel">Отменить</button>
                            </form>
                        @else
                            <div style="color:#bdbdbd; text-align:center; font-size:0.98rem; margin-top:4px;">Нет доступных действий</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <!-- Пагинация -->
        <div style="margin-top:32px; display:flex; justify-content:center;">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection