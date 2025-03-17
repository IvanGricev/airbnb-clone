@extends('layouts.main')
@section('title', 'Таблица: ' . $table) <!-- Исправлено $tableName → $table -->
@section('content')
<h1>Таблица: {{ $table }}</h1> <!-- Исправлено $tableName → $table -->
<a href="{{ route('admin.database.create', ['table' => $table]) }}" class="btn btn-success">Добавить запись</a>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table">
    <thead>
        <tr>
            @foreach($columns as $column)
                <th>{{ $column->Field }}</th>
            @endforeach
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                @foreach($columns as $column)
                    <td>{{ $row->{$column->Field} }}</td>
                @endforeach
                <td>
                    <a href="{{ route('admin.database.edit', ['table' => $table, 'id' => $row->id]) }}" class="btn btn-primary">Редактировать</a>
                    <form action="{{ route('admin.database.delete', ['table' => $table, 'id' => $row->id]) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $data->links() }} <!-- Пагинация -->
@endsection
