<link rel="stylesheet" href="{{ url('/css/main.css') }}">
@extends('layouts.main')

@section('title', 'Список жилья')

@section('content')
<div class="hero-main">
    <div class="hero-left">
        <h1>
            <span class="bold">Good Living</span> <span class="bold">Better Live</span><br>
            <span class="dreams">Your Dreams</span> <span class="bold">Easily Here</span>
        </h1>
        <p class="desc">
            Всё, что вам нужно для поиска места для аренды, будет здесь.<br>
            Наши предложения сделаны из отборных и лучших вариантов, которые подходят для аренды вашей мечты
        </p>
        <form class="search-form" action="{{ route('properties.index') }}" method="GET">
            <input type="text" name="query" placeholder="Что вы ищете?" />
            <button type="submit" class="search-btn">Поиск</button>
        </form>
    </div>
    <div class="hero-right">
        <div class="main-image">
            <img src="{{ asset('images/path_to_hero_image.jpg')}}" alt="Жилое пространство">
            <div class="mini-cards">
                <div class="mini-card active">
                    <img src="{{ asset('images/path_to_image_1.jpg')}}" alt="Manhattan Style">
                    <div>
                        <h3>Manhattan Style</h3>
                        <p>Lorem ipsum dolor sit amet, conser adipiscing elit</p>
                        <span class="rating">4.8 (400+ Review)</span>
                    </div>
                </div>
                <div class="mini-card">
                    <img src="{{ asset('images/path_to_image_2.jpg')}}" alt="New Future">
                    <div>
                        <h3>New Future</h3>
                        <p>Lorem ipsum dolor sit amet, conser adipiscing elit</p>
                        <span class="rating">4.5 (320+ Review)</span>
                    </div>
                </div>
            </div>
            <div class="slider-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>
</div>
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
                                <input class="form-check-input" type="checkbox" name="tags[]" id="tag{{ $tag->id }}" value="{{ $tag->id }}" 
                                    {{ in_array($tag->id, $selectedTags) ? 'checked' : '' }}>
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
                    <!-- Отображение изображения -->
                    @if($property->images->count() > 0)
                        <img src="{{ asset('storage/' . $property->images->first()->image_path) }}" class="card-img-top" alt="Превью">
                    @else
                        <img src="{{ asset('storage/default-placeholder.png') }}" class="card-img-top" alt="Нет изображения">
                    @endif
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
