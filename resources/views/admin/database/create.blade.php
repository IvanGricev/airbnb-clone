@extends('layouts.main')
@section('title', 'Добавить запись в таблицу ' . $tableName)
@section('content')
<h1>Добавить запись в таблицу: {{ $tableName }}</h1>

<form action="{{ route('admin.database.store', $tableName) }}" method="POST">
    @csrf
    @foreach($columns as $column)
        @if(!in_array($column, ['id', 'created_at', 'updated_at']))
            <div class="mb-3">
                <label for="{{ $column }}" class="form-label">{{ ucfirst($column) }}</label>
                <input type="text" name="{{ $column }}" class="form-control" id="{{ $column }}" value="{{ old($column) }}">
                @error($column)
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        @endif
    @endforeach
    <button type="submit" class="btn btn-success">Добавить</button>
</form>
@endsection
