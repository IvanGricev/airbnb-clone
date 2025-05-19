{{-- resources/views/admin/support/chat.blade.php --}}
@extends('layouts.main')
@section('title', 'Тикет #' . $ticket->id)
@section('content')
<link rel="stylesheet" href="{{ url('/css/chat.blade.css') }}">
<div class="ticket-chat-wrapper">
    <div style="flex:0 0 340px; max-width:340px; min-width:260px;">
        <div class="ticket-chat-userinfo mb-2">
            <div><strong>Пользователь:</strong> {{ $ticket->user->name }} ({{ $user->role }})</div>
            <div><strong>Email:</strong> {{ $user->email }}</div>
            <div><strong>Статус тикета:</strong> {{ $ticket->status }}</div>
        </div>
        <form action="{{ route('admin.support.updateStatus', $ticket->id) }}" method="POST" class="mb-3">
            @csrf
            @method('PUT')
            <div class="ticket-status-info mb-2">
                <label for="status" class="form-label">Изменить статус тикета</label>
                <select name="status" id="status" class="form-control">
                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Открыт</option>
                    <option value="answered" {{ $ticket->status == 'answered' ? 'selected' : '' }}>Отвечен</option>
                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Закрыт</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Обновить статус</button>
        </form>
    </div>
    <div style="flex:1 1 0; max-width:420px; min-width:280px; display: flex; justify-content: flex-end;">
        @if($bookings->isNotEmpty())
            @php $booking = $bookings->first(); $property = $booking->property; @endphp
            <div style="width: 100%; max-width: 370px; background: #fff; border-radius: 22px; box-shadow: 0 8px 40px rgba(30,64,175,0.10); overflow: hidden; display: flex; flex-direction: column; align-items: stretch;">
                <div style="width: 100%; height: 180px; background: #f3f3f3; overflow: hidden;">
                    <img src="{{ $property->images->first() ? asset('storage/' . $property->images->first()->image_path) : asset('images/user-placeholder.svg') }}" alt="{{ $property->title }}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>
                <div style="padding: 22px 22px 0 22px; background: #f8fafc; font-size: 1.18rem; font-weight: 700; color: #1e2351;">{{ $property->title }}</div>
                <div style="padding: 18px 22px 0 22px; background: #fff; font-size: 1.04rem; color: #232323;">
                    <div style="margin-bottom: 6px; color: #888;">Заезд: {{ $booking->start_date }}</div>
                    <div style="margin-bottom: 12px; color: #888;">Выезд: {{ $booking->end_date }}</div>
                    <div style="font-size: 1.25rem; font-weight: 800; margin-bottom: 12px; color: #232323;">{{ number_format($booking->total_price, 2, '.', ' ') }} руб.</div>
                    <div style="margin-bottom: 16px;">
                        <span style="background: #e6f9f0; color: #10B981; border-radius: 16px; padding: 7px 18px; font-size: 1.01rem; font-weight: 600;">{{ $booking->status == 'confirmed' ? 'Подтверждено' : $booking->status }}</span>
                    </div>
                </div>
                <div style="padding: 0 22px 22px 22px; background: #fff;">
                    <a href="{{ route('admin.support.between', ['user1' => $booking->user_id, 'user2' => $property->user_id]) }}" style="display:block; width:100%; background: #ffb43a; color: #fff; border-radius: 12px; font-size: 1.13rem; font-weight: 700; text-align: center; padding: 14px 0; text-decoration: none; margin-top: 8px; box-shadow: 0 2px 8px #2563eb11; transition: background 0.2s;">Чат</a>
                </div>
            </div>
        @else
            <div class="ticket-chat-bookings mb-2">У пользователя нет активных бронирований.</div>
        @endif
    </div>
</div>
@endsection
