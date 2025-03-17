@extends('layouts.main')
@section('title', 'Управление Базой Данных')
@section('content')
<h1>Таблицы базы данных</h1>
<ul>
    @foreach($tables as $currentTable)
        <li>
            <a href="{{ route('admin.database.table', ['table' => $currentTable]) }}">
                {{ $currentTable }}
            </a>
        </li>
    @endforeach
</ul>
@endsection
