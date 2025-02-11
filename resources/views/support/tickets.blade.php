@extends('layouts.main')

@section('title', 'Мои тикеты в поддержку')

@section('content')
<h1>Мои тикеты в поддержку</h1>

@if($tickets->isEmpty())
    <p>У вас пока нет тикетов.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Тема</th>
                <th>Дата создания</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket->subject }}</td>
                <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                <td>{{ $ticket->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
