@extends('layouts.error')

@section('title', 'Страница не найдена - 404')

@section('content')
    <div class="container">
        <div class="error-404">
            <h1>404</h1>
            <h2>Страница не найдена</h2>
            <p>Извините, но запрошенная страница не существует.</p>
            <a href="{{ url('/') }}" class="btn btn-primary">Вернуться на главную</a>
        </div>
    </div>
@endsection