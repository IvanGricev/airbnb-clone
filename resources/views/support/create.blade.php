@extends('layouts.main')

@section('title', 'Создать тикет поддержки')

@section('content')
<link rel="stylesheet" href="{{ asset('build/css/tickets.css') }}">
<div class="ticket-create-wrapper">
    <h1>Создать тикет поддержки</h1>

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
  <form action="{{ route('support.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="subject" class="form-label">Тема тикета</label>
            <input type="text" name="subject" id="subject" class="form-control" 
                   placeholder="Введите тему тикета" value="{{ old('subject') }}" required>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Сообщение</label>
            <textarea name="message" id="message" class="form-control" rows="5" 
                      placeholder="Опишите вашу проблему" required>{{ old('message') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Создать тикет</button>
    </form>
</div>
@endsection