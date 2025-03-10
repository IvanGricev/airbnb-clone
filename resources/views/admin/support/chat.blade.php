{{-- resources/views/admin/support/chat.blade.php --}}
@extends('layouts.main')
@section('title', 'Тикет #' . $ticket->id)
@section('content')
<h1>Тикет #{{ $ticket->id }}: {{ $ticket->subject }}</h1>
<p><strong>Пользователь:</strong> {{ $ticket->user->name }}</p>
<p><strong>Статус:</strong> {{ $ticket->status }}</p>

<form action="{{ route('admin.support.updateStatus', $ticket->id) }}" method="POST" class="mb-4">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label for="status" class="form-label">Изменить статус тикета</label>
        <select name="status" id="status" class="form-control">
            <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Открыт</option>
            <option value="answered" {{ $ticket->status == 'answered' ? 'selected' : '' }}>Отвечен</option>
            <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Закрыт</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Обновить статус</button>
</form>

<!-- Информация о пользователе -->
<h2>Информация о пользователе</h2>
<p><strong>Имя:</strong> {{ $user->name }}</p>
<p><strong>Email:</strong> {{ $user->email }}</p>
<p><strong>Роль:</strong> {{ $user->role }}</p>

<!-- Активные бронирования -->
<h3>Активные бронирования</h3>
@if($bookings->isEmpty())
    <p>У пользователя нет активных бронирований.</p>
@else
    <ul>
        @foreach($bookings as $booking)
            <li>
                <strong>Объект:</strong> {{ $booking->property->title }}<br>
                <strong>Период:</strong> {{ $booking->start_date }} - {{ $booking->end_date }}<br>
                <strong>Статус бронирования:</strong> {{ $booking->status }}<br>
                <!-- Кнопка для перехода в чат между арендатором и арендодателем -->
                <a href="{{ route('admin.chat.between', ['user1' => $booking->user_id, 'user2' => $booking->property->user_id]) }}" class="btn btn-info mt-2">Перейти в чат с арендодателем</a>
            </li>
        @endforeach
    </ul>
@endif

<div id="chat-messages" style="border: 1px solid #ccc; padding: 20px; height: 400px; overflow-y: scroll;">
    @foreach($messages as $message)
    <div class="mb-2">
        <strong>{{ $message->user->name }} ({{ $message->user->role }}):</strong><br>
        {{ $message->message }}<br>
        <small class="text-muted">{{ $message->created_at->format('d.m.Y H:i') }}</small>
    </div>
    <hr>
    @endforeach
</div>

<form action="{{ route('admin.support.message.send', $ticket->id) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="message" class="form-label">Сообщение</label>
        <textarea name="message" class="form-control" rows="4" required></textarea>
        @error('message')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>
@endsection
