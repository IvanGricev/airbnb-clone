@extends('layouts.main')

@section('title', 'Мои тикеты поддержки')

@section('content')
    <h1>Мои тикеты поддержки</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($tickets->isEmpty())
        <p>
            У вас пока нет тикетов. Вы можете создать новый, перейдя на 
            <a href="{{ route('support.create') }}">страницу создания тикета</a>.
        </p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Тема</th>
                    <th>Статус</th>
                    <th>Последнее обновление</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->id }}</td>
                        <td>{{ $ticket->subject }}</td>
                        <td>{{ ucfirst($ticket->status) }}</td>
                        <td>{{ $ticket->updated_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <a href="{{ route('support.show', $ticket->id) }}" class="btn btn-primary btn-sm">
                                Просмотреть
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
