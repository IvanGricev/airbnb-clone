@extends('layouts.main')
@section('title', 'Таблица: ' . $table) <!-- Исправлено $tableName → $table -->
@section('content')
<link rel="stylesheet" href="{{ url('/css/table.blade.admin.css') }}">
<div class="db-table-container">
    <div class="db-table-card">
        <div class="db-table-header">
            <h1>Таблица: {{ $table }}</h1>
            <a href="{{ route('admin.database.create', ['table' => $table]) }}" class="btn btn-success">Добавить запись</a>
        </div>
        <div class="db-table-divider"></div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="db-table-responsive">
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
        </div>
        <div class="pagination">
            {{ $data->links() }} <!-- Пагинация -->
        </div>
    </div>
</div>
@endsection
