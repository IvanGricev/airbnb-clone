@extends('layouts.main')
@section('title', 'Тикет #' . $ticket->id)
@section('content')
<h1>Тикет #{{ $ticket->id }}: {{ $ticket->subject }}</h1>
<div id="chat-messages" style="border: 1px solid #ccc; padding: 20px; height: 400px; overflow-y: scroll;">
    @foreach($messages as $message)
    <div>
        <strong>{{ $message->user->name }}:</strong> {{ $message->message }}
        <small class="text-muted">{{ $message->created_at->format('H:i d.m.Y') }}</small>
    </div>
    @endforeach
</div>
<form action="{{ route('support.message.send', $ticket->id) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="message" class="form-label">Сообщение</label>
        <textarea name="message" class="form-control" required></textarea>
        @error('message')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>

<!-- Логика обновления сообщений в реальном времени -->
<script src="{{ mix('js/app.js') }}"></script>
<script>
    Echo.private('ticket.{{ $ticket->id }}')
        .listen('SupportMessageSent', (e) => {
            let messageContainer = document.getElementById('chat-messages');
            messageContainer.innerHTML += `
                <div>
                    <strong>${e.message.user.name}:</strong> ${e.message.message}
                    <small class="text-muted">${new Date(e.message.created_at).toLocaleString()}</small>
                </div>
            `;
            messageContainer.scrollTop = messageContainer.scrollHeight;
        });
</script>
@endsection
