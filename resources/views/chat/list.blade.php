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
                <a href="{{ route('support.tickets.show', $ticket->id) }}">Тикет #{{ $ticket->id }}: {{ $ticket->subject }}</a>
            </li>
        @endforeach
    </ul>
@endif
@endsection
