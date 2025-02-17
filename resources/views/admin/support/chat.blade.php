{{-- resources/views/admin/support/chat.blade.php --}}
@extends('layouts.main')
@section('title', 'Тикет #' . $ticket->id)
@section('content')
<h1>Тикет #{{ $ticket->id }}: {{ $ticket->subject }}</h1>
<p><strong>Пользователь:</strong> {{ $ticket->user->name }}</p>
<p><strong>Статус:</strong> {{ $ticket->status }}</p>

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
