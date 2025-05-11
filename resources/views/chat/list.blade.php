@extends('layouts.main')

@section('title', 'Мои чаты')

@section('content')
<link rel="stylesheet" href="{{ url('/css/tickets.css') }}">
<div class="tickets-header">
    <h1 class="tickets-title">Мои чаты и обращения</h1>
    <div class="tickets-desc">
        Здесь вы можете просматривать свои диалоги с пользователями и обращения в поддержку. Все ваши коммуникации — в одном месте.
    </div>
</div>

<div class="tickets-list">
    <div class="tickets-section">
        <h2 class="tickets-section-title">Чаты с пользователями</h2>
        @if($users->isEmpty())
            <div class="tickets-empty">У вас нет сообщений с другими пользователями.</div>
        @else
            <ul class="tickets-ul">
                @foreach($users as $user)
                    <li class="ticket-card ticket-chat-card">
                        <a href="{{ route('chat.index', ['withUserId' => $user->id]) }}">
                            <div class="ticket-card-title">{{ $user->name }}</div>
                            <div class="ticket-card-meta">Личный чат</div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="tickets-section">
        <h2 class="tickets-section-title">Обращения в поддержку</h2>
        @if($supportTickets->isEmpty())
            <div class="tickets-empty">У вас нет обращений в поддержку.</div>
        @else
            <ul class="tickets-ul">
                @foreach($supportTickets as $ticket)
                    <li class="ticket-card ticket-support-card">
                        <a href="{{ route('support.show', $ticket->id) }}">
                            <div class="ticket-card-title">Тикет #{{ $ticket->id }}: {{ $ticket->subject }}</div>
                            <div class="ticket-card-meta">Статус: {{ $ticket->status ?? 'Открыт' }}</div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
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
