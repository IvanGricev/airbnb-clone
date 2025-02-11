@extends('layouts.main')

@section('title', 'Чат')

@section('content')
<h1>Чат с {{ $withUser->name }}</h1>

<div id="chat-messages" style="border: 1px solid #ccc; padding: 20px; height: 300px; overflow-y: scroll;">
    @foreach($messages as $message)
        <div>
            <strong>{{ $message->fromUser->name }}:</strong> {{ $message->content }}
            <small class="text-muted">{{ $message->created_at->format('H:i d.m.Y') }}</small>
        </div>
    @endforeach
</div>

<form action="{{ route('messages.send') }}" method="POST">
    @csrf
    <input type="hidden" name="to_user_id" value="{{ $withUser->id }}">
    <div class="mb-3">
        <label for="content" class="form-label">Сообщение</label>
        <textarea name="content" class="form-control"></textarea>
        @error('content') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>
@endsection
