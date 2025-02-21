@extends('layouts.main')

@section('title', 'Список жилья')

@section('content')
<h1>Список жилья</h1>

<!-- Фильтр по тегам -->
<form action="{{ route('properties.search') }}" method="GET" class="mb-4">
    <input type="hidden" name="query" value="{{ request('query') }}">
    <div class="mb-3">
        <label for="tags" class="form-label">Фильтр по тегам (через запятую)</label>
        <input type="text" name="tags" class="form-control" value="{{ old('tags', $tagFilter ?? '') }}">
    </div>
    <button type="submit" class="btn btn-primary">Применить фильтр</button>
</form>

@if($properties->isEmpty())
    <p>Жильё не найдено.</p>
@else
    <div class="row">
        @foreach($properties as $property)
            <div class="col-md-4">
                <div class="card mb-4">
                    <!-- Здесь можно добавить изображение жилья -->
                    <div class="card-body">
                        <h5 class="card-title">{{ $property->title }}</h5>
                        <p class="card-text">{{ Str::limit($property->description, 100) }}</p>
                        @if($property->tags->isNotEmpty())
                            <p>
                                @foreach($property->tags as $tag)
                                    <span class="badge bg-secondary">{{ $tag->name }}</span>
                                @endforeach
                            </p>
                        @endif
                        <a href="{{ route('properties.show', $property->id) }}" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
