@extends('layouts.main')
@section('title', 'Редактировать запись в таблице ' . $tableName)
@section('content')
<h1>Редактировать запись в таблице: {{ $tableName }}</h1>

<form action="{{ route('admin.database.update', [$tableName, $row->id]) }}" method="POST">
    @csrf
    @method('PUT')
    @foreach($columns as $column)
        @if(!in_array($column, ['id', 'created_at', 'updated_at']))
            <div class="mb-3">
                <label for="{{ $column }}" class="form-label">{{ ucfirst($column) }}</label>
                <input type="text" name="{{ $column }}" class="form-control" id="{{ $column }}" value="{{ old($column, $row->$column) }}">
                @error($column)
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        @else
            <input type="hidden" name="{{ $column }}" value="{{ $row->$column }}">
        @endif
    @endforeach
    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
</form>
@endsection
