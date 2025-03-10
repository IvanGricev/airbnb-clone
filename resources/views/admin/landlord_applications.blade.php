@extends('layouts.main')

@section('title', 'Заявки на роль арендодателя')

@section('content')
<h1>Заявки на роль арендодателя</h1>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($applications->isEmpty())
    <p>Нет новых заявок.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Пользователь</th>
                <th>Сообщение</th>
                <th>Дата подачи</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $application)
            <tr>
                <td>{{ $application->user->name }} ({{ $application->user->email }})</td>
                <td>{{ $application->message }}</td>
                <td>{{ $application->created_at->format('d.m.Y H:i') }}</td>
                <td>
                    <form action="{{ route('admin.landlord.applications.approve', $application) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-success btn-sm">Одобрить</button>
                    </form>
                    <form action="{{ route('admin.landlord.applications.reject', $application) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-danger btn-sm">Отклонить</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
