@extends('layouts.main')
@section('title', 'Таблица: ' . $table)
@section('content')
<link rel="stylesheet" href="{{ asset('css/table.blade.admin.css') }}">
<div class="db-table-container">
    <div class="db-table-card">
        <div class="db-table-header">
            <h1>Таблица: {{ $table }}</h1>
            <a href="{{ route('admin.database.create', ['table' => $table]) }}" class="btn btn-success">Добавить запись</a>
        </div>
        <div class="db-table-divider"></div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="db-table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th>{{ $column->column_name }}</th>
                        @endforeach
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr>
                            @foreach($columns as $column)
                                <td>{{ $row->{$column->column_name} }}</td>
                            @endforeach
                            <td>
                                <a href="{{ route('admin.database.edit', ['table' => $table, 'id' => $row->id]) }}" class="btn btn-primary">Редактировать</a>
                                <form action="{{ route('admin.database.delete', ['table' => $table, 'id' => $row->id]) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту запись?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + 1 }}" class="text-center">
                                Нет данных в таблице
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center my-3">
            <div>
                <small>
                    Показано {{ $data->firstItem() }}–{{ $data->lastItem() }} из {{ $data->total() }} записей
                </small>
            </div>
            <div>
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
