@extends('layouts.main')

@section('title', 'Список жилья')

@section('content')
<h1>Список жилья</h1>

<form action="{{ route('properties.index') }}" method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-4 mb-3">
            <input type="text" name="query" class="form-control" placeholder="Что ищем?" value="{{ request('query') }}">
        </div>
        <div class="col-md-2 mb-3">
            <input type="number" name="min_price" class="form-control" placeholder="Минимальная цена" value="{{ request('min_price') }}">
        </div>
        <div class="col-md-2 mb-3">
            <input type="number" name="max_price" class="form-control" placeholder="Максимальная цена" value="{{ request('max_price') }}">
        </div>
        <div class="col-md-4 mb-3">
            <select name="sort_order" class="form-control">
                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>От дешевого к дорогому</option>
                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>От дорогого к дешевому</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @foreach($tags as $category => $tagsGroup)
                <div class="mb-3">
                    <label class="form-label">{{ $category }}</label>
                    <div>
                        @foreach($tagsGroup as $tag)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="tags[]" id="tag{{ $tag->id }}" value="{{ $tag->id }}" {{ in_array($tag->id, $selectedTags) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tag{{ $tag->id }}">{{ $tag->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Найти</button>
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
                        <p>
                            @if($property->reviews->count() > 0)
                                Средний рейтинг: {{ $property->average_rating }} из 5
                            @else
                                Нет оценок
                            @endif
                        </p>
                        <p class="card-text">{{ Str::limit($property->description, 100) }}</p>
                        @if($property->tags->isNotEmpty())
                            <p>
                                @foreach($property->tags as $tag)
                                    <span class="badge bg-secondary">{{ $tag->name }}</span>
                                @endforeach
                            </p>
                        @endif
                        <p><strong>Цена за ночь:</strong> {{ $property->price_per_night }} руб.</p>
                        <a href="{{ route('properties.show', $property->id) }}" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Пагинация -->
    {{ $properties->links() }}
@endif
@endsection