@extends('layouts.main')
@section('title', 'Управление Базой Данных')
@section('content')
<link rel="stylesheet" href="{{ asset('css/tabeladmin.css') }}">
<div class="db-admin-container">
    <div class="db-admin-header">
        <h1>Таблицы базы данных</h1>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="db-table-list">
        @forelse($tables as $currentTable)
            <a href="{{ route('admin.database.table', ['table' => $currentTable]) }}" class="db-table-btn">
                {{ $currentTable }}
            </a>
        @empty
            <div class="alert alert-info">
                Нет доступных таблиц в базе данных.
            </div>
        @endforelse
    </div>
</div>
@endsection
