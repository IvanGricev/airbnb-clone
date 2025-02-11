@extends('layouts.main')

@section('title', 'Список жилья')

@section('content')
<h1>Список жилья</h1>

<div class="row">
    @foreach($properties as $property)
        <div class="col-md-4">
            <div class="card mb-4">
                <!-- Здесь можно добавить изображение -->
                <div class="card-body">
                    <h5 class="card-title">{{ $property->title }}</h5>
                    <p class="card-text">{{ Str::limit($property->description, 100) }}</p>
                    <a href="{{ route('properties.show', $property) }}" class="btn btn-primary">Подробнее</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
