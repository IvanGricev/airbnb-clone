@extends('layouts.main')

@section('title', 'Мои чаты')

@section('content')
<h1>Мои чаты</h1>

<h2>Чаты с пользователями</h2>
@if($users->isEmpty())
    <p>У вас нет сообщений с другими пользователями.</p>
@else
    <ul>
        @foreach($users as $user)
            <li>
                <a href="{{ route('chat.index', ['withUserId' => $user->id]) }}">{{ $user->name }}</a>
            </li>
        @endforeach
    </ul>
@endif

<h2>Поддержка</h2>
@if($supportTickets->isEmpty())
    <p>У вас нет обращений в поддержку.</p>
@else
    <ul>
        @foreach($supportTickets as $ticket)
            <li>
                <a href="{{ route('support.show', $ticket->id) }}">Тикет #{{ $ticket->id }}: {{ $ticket->subject }}</a>
            </li>
        @endforeach
    </ul>
@endif

<!-- Логика обновления сообщений в реальном времени -->
<script src="{{ mix('js/app.js') }}"></script>
<script>
    Echo.private('user.{{ Auth::id() }}')
        .listen('MessageSent', (e) => {
            // Логика обновления списка чатов или уведомлений
        });

    Echo.private('support.{{ Auth::id() }}')
        .listen('SupportMessageSent', (e) => {
            // Логика обновления списка тикетов или уведомлений
        });
</script>
@endsection
