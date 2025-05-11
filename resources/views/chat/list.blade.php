@extends('layouts.main')

@section('title', 'Мои чаты и обращения')

@section('content')
<link rel="stylesheet" href="{{ url('/css/allchats.css') }}">
<div class="allchats-layout">
    <aside class="allchats-sidebar">
        <div class="allchats-sidebar-title">Чаты</div>
        <ul class="allchats-chats-list">
            @forelse($users as $user)
                <li>
                    <a href="{{ route('chat.index', ['withUserId' => $user->id]) }}" class="allchats-chat-link">
                        {{ $user->name }}
                    </a>
                </li>
            @empty
                <li class="allchats-empty">Нет чатов</li>
            @endforelse
        </ul>
    </aside>
    <main class="allchats-main">
        <div class="allchats-support-title">Обращения в поддержку</div>
        @if($supportTickets->isEmpty())
            <div class="allchats-empty">У вас нет обращений в поддержку.</div>
        @else
            <div class="allchats-support-grid">
                @foreach($supportTickets as $ticket)
                    <div class="allchats-support-card">
                        <div class="allchats-support-card-title">Тикет #{{ $ticket->id }}</div>
                        <div class="allchats-support-card-subject">{{ $ticket->subject }}</div>
                        <div class="allchats-support-card-status">Статус: {{ $ticket->status ?? 'Открыт' }}</div>
                        <a href="{{ route('support.show', $ticket->id) }}" class="allchats-support-card-link">Подробнее</a>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</div>
@endsection
