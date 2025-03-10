@extends('layouts.main')

@section('title', 'Чат с ' . $withUser->name)

@section('content')
<h1>Чат с {{ $withUser->name }}</h1>

<div id="chat-messages" style="border: 1px solid #ccc; padding: 20px; height: 300px; overflow-y: scroll;">
    @foreach($messages as $message)
        <div>
            <strong>{{ $message->fromUser->name }}:</strong> {{ $message->content }}
            <small class="text-muted">{{ $message->created_at->format('H:i d.m.Y') }}</small>
        </div>
    @endforeach
</div>

<form action="{{ route('messages.send') }}" method="POST">
    @csrf
    <input type="hidden" name="to_user_id" value="{{ $withUser->id }}">
    <div class="mb-3">
        <label for="content" class="form-label">Сообщение</label>
        <textarea name="content" class="form-control" rows="3" required></textarea>
        @error('content') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>

<!-- Логика обновления сообщений в реальном времени -->
<script src="{{ mix('js/app.js') }}"></script>
<script>
    Echo.private('chat.{{ Auth::id() }}.{{ $withUser->id }}')
        .listen('MessageSent', (e) => {
            let messageContainer = document.getElementById('chat-messages');
            messageContainer.innerHTML += `
                <div>
                    <strong>${e.message.fromUser.name}:</strong> ${e.message.content}
                    <small class="text-muted">${new Date(e.message.created_at).toLocaleString()}</small>
                </div>
            `;
            messageContainer.scrollTop = messageContainer.scrollHeight;
        });
</script>
@endsection
