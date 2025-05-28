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
        <div class="chat-form-row">
            <textarea name="content" class="chat-form-input" rows="2" placeholder="Введите сообщение..." required></textarea>
            <button type="submit" class="chat-form-send">Отправить</button>
        </div>
        @error('content') <div class="chat-form-error">{{ $message }}</div> @enderror
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
