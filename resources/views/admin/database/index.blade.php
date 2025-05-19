@extends('layouts.main')
@section('title', 'Управление Базой Данных')
@section('content')
<link rel="stylesheet" href="{{ url('/css/tabeladmin.css') }}">
<div class="db-admin-container">
    <div class="db-admin-header">
        <h1>Таблицы базы данных</h1>
    </div>
    <div class="db-table-list">
        @foreach($tables as $currentTable)
            <a href="{{ route('admin.database.table', ['table' => $currentTable]) }}" class="db-table-btn">
                {{ $currentTable }}
            </a>
        @endforeach
    </div>
</div>
@endsection
