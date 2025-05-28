@extends('layouts.main')

@section('title', 'Чат с ' . $withUser->name)

@section('content')
<link rel="stylesheet" href="{{ asset('/css/chat.css') }}">
<div class="chat-wrapper">
    <h1 class="chat-title">Чат с {{ $withUser->name }}</h1>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="chat-container">
        <div id="chat-messages" class="chat-messages">
            @foreach($messages as $message)
                <div class="chat-message-row {{ $message->from_user_id === Auth::id() ? 'chat-message-own' : 'chat-message-other' }}">
                    <div class="chat-message-bubble">
                        <div class="chat-message-content">{{ $message->content }}</div>
                        <div class="chat-message-meta">
                            <span class="chat-message-author">{{ $message->from_user_id === Auth::id() ? 'Вы' : $message->fromUser->name }}</span>
                            <span class="chat-message-time">{{ $message->created_at->format('H:i d.m.Y') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form action="{{ route('messages.send') }}" method="POST" class="chat-form">
            @csrf
            <input type="hidden" name="to_user_id" value="{{ $withUser->id }}">
            <div class="chat-input-container">
                <input type="text" name="content" class="chat-input" placeholder="Введите сообщение..." required>
                <button type="submit" class="chat-send-button">Отправить</button>
            </div>
            @error('content') <div class="chat-form-error">{{ $message }}</div> @enderror
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Прокрутка к последнему сообщению при загрузке
        const messageContainer = document.getElementById('chat-messages');
        messageContainer.scrollTop = messageContainer.scrollHeight;

        // Обработка отправки формы
        const form = document.querySelector('.chat-form');
        const input = form.querySelector('.chat-input');
        
        form.addEventListener('submit', function(e) {
            if (!input.value.trim()) {
                e.preventDefault();
                return;
            }
        });

        // Обработка Enter для отправки
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.submit();
            }
        });

        // Обработка новых сообщений через Echo
        if (typeof Echo !== 'undefined') {
            Echo.private('chat.{{ Auth::id() }}.{{ $withUser->id }}')
                .listen('MessageSent', (e) => {
                    let messageContainer = document.getElementById('chat-messages');
                    let isOwn = e.message.from_user_id === {{ Auth::id() }};
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
                    input.value = '';
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

<style>
.chat-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.chat-title {
    text-align: center;
    margin-bottom: 20px;
}

.chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f9f9f9;
}

.chat-message-row {
    margin-bottom: 15px;
    display: flex;
}

.chat-message-own {
    justify-content: flex-end;
}

.chat-message-bubble {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.chat-message-own .chat-message-bubble {
    background: #007bff;
    color: #fff;
}

.chat-message-content {
    margin-bottom: 5px;
    word-wrap: break-word;
}

.chat-message-meta {
    font-size: 0.8em;
    color: #666;
    display: flex;
    justify-content: space-between;
}

.chat-message-own .chat-message-meta {
    color: #e6e6e6;
}

.chat-form {
    padding: 15px;
    background: #fff;
    border-top: 1px solid #ddd;
}

.chat-input-container {
    display: flex;
    gap: 10px;
}

.chat-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.chat-send-button {
    padding: 10px 20px;
    background: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.chat-send-button:hover {
    background: #0056b3;
}

.chat-form-error {
    color: #dc3545;
    font-size: 0.9em;
    margin-top: 5px;
}

.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>
@endsection
