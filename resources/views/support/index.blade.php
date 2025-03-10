@extends('layouts.main')

@section('title', 'Поддержка')

@section('content')
<h1>Обратиться в поддержку</h1>
<form action="{{ route('support.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="subject" class="form-label">Тема</label>
        <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
        @error('subject') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="message" class="form-label">Сообщение</label>
        <textarea name="message" class="form-control" required>{{ old('message') }}</textarea>
        @error('message') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>

<!-- Логика обновления сообщений в реальном времени -->
<script src="{{ mix('js/app.js') }}"></script>
<script>
    Echo.private('support.{{ Auth::id() }}')
        .listen('SupportTicketCreated', (e) => {
            alert('Ваш тикет успешно создан. Пожалуйста, ожидайте ответа.');
        });
</script>
@endsection
