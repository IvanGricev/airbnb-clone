{{-- resources/views/admin/support/index.blade.php --}}
@extends('layouts.main')
@section('title', 'Тикеты поддержки')
@section('content')
<h1>Тикеты поддержки</h1>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Тема</th>
            <th>Пользователь</th>
            <th>Статус</th>
            <th>Дата обновления</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $ticket)
        <tr>
            <td>{{ $ticket->id }}</td>
            <td>{{ $ticket->subject }}</td>
            <td>{{ $ticket->user->name }}</td>
            <td>{{ $ticket->status }}</td>
            <td>{{ $ticket->updated_at->format('d.m.Y H:i') }}</td>
            <td>
                <a href="{{ route('admin.support.show', $ticket->id) }}" class="btn btn-primary">Открыть</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
