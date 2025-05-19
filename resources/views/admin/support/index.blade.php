{{-- resources/views/admin/support/index.blade.php --}}
@extends('layouts.main')
@section('title', 'Тикеты поддержки')
@section('content')
<link rel="stylesheet" href="{{ url('/css/tiketadmin.css') }}">
<div class="tickets-admin-container">
    <div class="tickets-admin-header">
        <h1>Тикеты поддержки</h1>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="tickets-admin-grid">
        @foreach($tickets as $ticket)
            <div class="ticket-admin-card">
                <div class="ticket-admin-avatar-block">
                    <img src="{{ asset('images/user-placeholder.svg') }}" alt="user" class="ticket-admin-avatar">
                </div>
                <div class="ticket-admin-card-content">
                    <div class="ticket-admin-name">{{ $ticket->user->name }}</div>
                    <div class="ticket-admin-email">{{ $ticket->user->email }}</div>
                    <div class="ticket-admin-subject">{{ $ticket->subject }}</div>
                    <div class="ticket-admin-date">Дата обновления: <b>{{ $ticket->updated_at->format('d.m.Y H:i') }}</b></div>
                    <div class="ticket-admin-status">
                        @if($ticket->status == 'open') Открыт
                        @elseif($ticket->status == 'answered') Отвечен
                        @elseif($ticket->status == 'closed') Закрыт
                        @else {{ $ticket->status }}
                        @endif
                    </div>
                    <a href="{{ route('admin.support.show', $ticket->id) }}" class="ticket-admin-btn">Открыть</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
