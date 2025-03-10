@extends('layouts.main')
@section('title', 'Чат между пользователями')
@section('content')
<h1>Чат между {{ $userOne->name }} и {{ $userTwo->name }}</h1>

<div id="chat-messages" style="border: 1px solid #ccc; padding: 20px; height: 400px; overflow-y: scroll;">
    @foreach($messages as $message)
        <div class="mb-2">
            <strong>{{ $message->fromUser->name }}:</strong> {{ $message->content }}<br>
            <small class="text-muted">{{ $message->created_at->format('d.m.Y H:i') }}</small>
        </div>
        <hr>
    @endforeach
</div>

<!-- возможность отправить сообщение от имени администратора -->
<!--
<form action="{{ route('admin.chat.sendMessage', ['user1' => $userOne->id, 'user2' => $userTwo->id]) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="content" class="form-label">Сообщение</label>
        <textarea name="content" class="form-control"></textarea>
        @error('content')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>
-->
@endsection
