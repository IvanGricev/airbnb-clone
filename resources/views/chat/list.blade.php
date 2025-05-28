@extends('layouts.main')

@section('title', 'Мои чаты и обращения')

@section('content')
<link rel="stylesheet" href="{{ asset('/css/allchats.css') }}">
<div class="allchats-layout">
    <aside class="allchats-sidebar">
        <div class="allchats-sidebar-title">Чаты</div>
        <ul class="allchats-chats-list">
            @forelse($users as $user)
                <li class="allchats-chat-item">
                    <a href="{{ route('chat.index', ['withUserId' => $user->id]) }}" class="allchats-chat-link">
                        <div class="allchats-chat-avatar">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="allchats-chat-info">
                            <div class="allchats-chat-name">{{ $user->name }}</div>
                            @if($user->messages->isNotEmpty())
                                <div class="allchats-chat-preview">
                                    {{ Str::limit($user->messages->first()->content, 50) }}
                                </div>
                                <div class="allchats-chat-time">
                                    {{ $user->messages->first()->created_at->format('H:i d.m.Y') }}
                                </div>
                            @endif
                        </div>
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
                        @if($ticket->supportMessages->isNotEmpty())
                            <div class="allchats-support-card-preview">
                                {{ Str::limit($ticket->supportMessages->first()->content, 100) }}
                            </div>
                        @endif
                        <a href="{{ route('support.show', $ticket->id) }}" class="allchats-support-card-link">Подробнее</a>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</div>

<style>
.allchats-chat-item {
    margin-bottom: 10px;
}

.allchats-chat-link {
    display: flex;
    align-items: center;
    padding: 10px;
    text-decoration: none;
    color: inherit;
    border-radius: 8px;
    transition: background-color 0.2s;
}

.allchats-chat-link:hover {
    background-color: #f5f5f5;
}

.allchats-chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #ffdb3a;
    color: #232323;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
}

.allchats-chat-info {
    flex: 1;
    min-width: 0;
}

.allchats-chat-name {
    font-weight: 600;
    margin-bottom: 4px;
}

.allchats-chat-preview {
    font-size: 0.9em;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.allchats-chat-time {
    font-size: 0.8em;
    color: #888;
    margin-top: 2px;
}

.allchats-support-card {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 12px 0 rgba(0,0,0,0.04);
}

.allchats-support-card-preview {
    margin: 10px 0;
    font-size: 0.9em;
    color: #666;
    line-height: 1.4;
}

.allchats-empty {
    color: #666;
    text-align: center;
    padding: 20px;
    font-style: italic;
}
</style>
@endsection
