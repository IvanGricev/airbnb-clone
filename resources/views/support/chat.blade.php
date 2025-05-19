@extends('layouts.main')
@section('title', 'Тикет #' . $ticket->id)
@section('content')
<link rel="stylesheet" href="{{ url('/css/chat.css') }}">
<div class="chat-wrapper">
    <h1 class="chat-title">Тикет #{{ $ticket->id }}: {{ $ticket->subject }}</h1>
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
    <form action="{{ route('support.message.send', $ticket->id) }}" method="POST" class="chat-form">
        @csrf
        <div class="chat-form-row">
            <textarea name="message" class="chat-form-input" rows="2" placeholder="Введите сообщение..." required></textarea>
            <button type="submit" class="chat-form-send">Отправить</button>
        </div>
        @error('message') <div class="chat-form-error">{{ $message }}</div> @enderror
    </form>
</div>
<script src="{{ mix('js/app.js') }}"></script>
<script>
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
        });
</script>
@endsection
