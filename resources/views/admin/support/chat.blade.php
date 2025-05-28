{{-- resources/views/admin/support/chat.blade.php --}}
@extends('layouts.main')
@section('title', 'Тикет #' . $ticket->id)
@section('content')
<link rel="stylesheet" href="{{ asset('/css/chat.css') }}">
<div class="chat-wrapper">
    <h1 class="chat-title">Тикет #{{ $ticket->id }}: {{ $ticket->subject }}</h1>
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="admin-chat-container">
        <!-- Информация о пользователе и статус тикета -->
        <div class="admin-chat-sidebar">
            <div class="ticket-chat-userinfo mb-2">
                <div><strong>Пользователь:</strong> {{ $ticket->user->name }} ({{ $ticket->user->role }})</div>
                <div><strong>Email:</strong> {{ $ticket->user->email }}</div>
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

            @if($bookings->isNotEmpty())
                @php $booking = $bookings->first(); $property = $booking->property; @endphp
                <div class="booking-info-card">
                    <div class="booking-image">
                        <img src="{{ $property->images->first() ? asset('storage/' . $property->images->first()->image_path) : asset('images/user-placeholder.svg') }}" 
                             alt="{{ $property->title }}">
                    </div>
                    <div class="booking-details">
                        <h3>{{ $property->title }}</h3>
                        <p>Заезд: {{ $booking->start_date }}</p>
                        <p>Выезд: {{ $booking->end_date }}</p>
                        <p class="booking-price">{{ number_format($booking->total_price, 2, '.', ' ') }} руб.</p>
                        <span class="booking-status {{ $booking->status }}">{{ $booking->status == 'confirmed' ? 'Подтверждено' : $booking->status }}</span>
                    </div>
                </div>
            @else
                <div class="ticket-chat-bookings mb-2">У пользователя нет активных бронирований.</div>
            @endif
        </div>

        <!-- Чат -->
        <div class="chat-main">
            <div id="chat-messages" class="chat-messages">
                @foreach($messages as $message)
                    <div class="chat-message-row {{ $message->user_id === Auth::id() ? 'chat-message-own' : 'chat-message-other' }}">
                        <div class="chat-message-bubble">
                            <div class="chat-message-content">{{ $message->message }}</div>
                            <div class="chat-message-meta">
                                <span class="chat-message-author">{{ $message->user_id === Auth::id() ? 'Вы' : $message->user->name }}</span>
                                <span class="chat-message-time">{{ $message->created_at->format('H:i d.m.Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('admin.support.message.send', $ticket->id) }}" method="POST" class="chat-form">
                @csrf
                <div class="chat-form-row">
                    <textarea name="message" class="chat-form-input" rows="2" placeholder="Введите сообщение..." required></textarea>
                    <button type="submit" class="chat-form-send">Отправить</button>
                </div>
                @error('message') <div class="chat-form-error">{{ $message }}</div> @enderror
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Echo !== 'undefined') {
            Echo.private('ticket.{{ $ticket->id }}')
                .listen('SupportMessageSent', (e) => {
                    let messageContainer = document.getElementById('chat-messages');
                    let isOwn = e.message.user_id === {{ Auth::id() }};
                    messageContainer.innerHTML += `
                        <div class="chat-message-row ${isOwn ? 'chat-message-own' : 'chat-message-other'}">
                            <div class="chat-message-bubble">
                                <div class="chat-message-content">${e.message.message}</div>
                                <div class="chat-message-meta">
                                    <span class="chat-message-author">${isOwn ? 'Вы' : e.message.user.name}</span>
                                    <span class="chat-message-time">${new Date(e.message.created_at).toLocaleString()}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    messageContainer.scrollTop = messageContainer.scrollHeight;
                })
                .error((error) => {
                    console.error('Echo error:', error);
                });
        } else {
            console.error('Echo is not defined. Make sure Laravel Echo is properly configured.');
        }
    });
</script>
@endpush
@endsection
