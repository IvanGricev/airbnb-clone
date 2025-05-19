@extends('layouts.main')
@section('title', 'Чат между пользователями')
@section('content')
<link rel="stylesheet" href="{{ url('/css/chat.css') }}">
<div class="chat-wrapper">
    <h1 class="chat-title">Чат между {{ $userOne->name }} и {{ $userTwo->name }}</h1>
    <div id="chat-messages" class="chat-messages">
        @foreach($messages as $message)
            <div class="chat-message-row {{ $message->fromUser->id === Auth::id() ? 'chat-message-own' : 'chat-message-other' }}">
                <div class="chat-message-bubble">
                    <div class="chat-message-content">{{ $message->content }}</div>
                    <div class="chat-message-meta">
                        <span class="chat-message-author">{{ $message->fromUser->id === Auth::id() ? 'Вы' : $message->fromUser->name }}</span>
                        <span class="chat-message-time">{{ $message->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <form action="{{ route('admin.chat.sendMessage', ['user1' => $userOne->id, 'user2' => $userTwo->id]) }}" method="POST" class="chat-form">
        @csrf
        <div class="chat-form-row">
            <textarea name="content" class="chat-form-input" rows="2" placeholder="Введите сообщение..." required></textarea>
            <button type="submit" class="chat-form-send">Отправить</button>
        </div>
        @error('content') <div class="chat-form-error">{{ $message }}</div> @enderror
    </form>
</div>
<script src="{{ mix('js/app.js') }}"></script>
<script>
    Echo.channel('chat.{{ $userOne->id }}.{{ $userTwo->id }}')
        .listen('MessageSent', (e) => {
            let messageContainer = document.getElementById('chat-messages');
            let isOwn = e.message.fromUser.id === {{ Auth::id() }};
            messageContainer.innerHTML += `
                <div class="chat-message-row ${isOwn ? 'chat-message-own' : 'chat-message-other'}">
                    <div class="chat-message-bubble">
                        <div class="chat-message-content">${e.message.content}</div>
                        <div class="chat-message-meta">
                            <span class="chat-message-author">${isOwn ? 'Вы' : e.message.fromUser.name}</span>
                            <span class="chat-message-time">${new Date(e.message.created_at).toLocaleString()}</span>
                        </div>
                    </div>
                </div>
            `;
            messageContainer.scrollTop = messageContainer.scrollHeight;
        });
</script>
@endsection
