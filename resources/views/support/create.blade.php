@extends('layouts.main')

@section('title', 'Создать тикет поддержки')

@section('content')
<link rel="stylesheet" href="{{ url('/css/tickets.css') }}">
<div class="ticket-create-wrapper">
    <h1 class="tickets-title">Создать тикет поддержки</h1>

    <!-- Уведомления об успехе или ошибке -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Вывод ошибок валидации -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Форма создания тикета -->
    <form action="{{ route('support.store') }}" method="POST" class="ticket-create-form">
        @csrf
        <div class="form-group">
            <label for="subject">Тема тикета</label>
            <input type="text" name="subject" id="subject" class="form-input" 
                   placeholder="Введите тему тикета" value="{{ old('subject') }}" required>
        </div>

        <div class="form-group">
            <label for="message">Сообщение</label>
            <textarea name="message" id="message" class="form-input" rows="5" 
                      placeholder="Опишите вашу проблему" required>{{ old('message') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Создать тикет</button>
    </form>
</div>
@endsection