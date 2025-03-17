@extends('layouts.main')
@section('title', 'Добавить жильё')
@section('content')
<div class="container mt-4">
    <h1>Добавить жильё</h1>
    <form action="{{ route('properties.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Название жилья -->
        <div class="mb-3">
            <label for="title" class="form-label">Название</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
            @error('title')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Описание -->
        <div class="mb-3">
            <label for="description" class="form-label">Описание</label>
            <textarea name="description" id="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Адрес -->
        <div class="mb-3">
            <label for="address" class="form-label">Адрес</label>
            <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}" required>
            @error('address')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Цена за ночь -->
        <div class="mb-3">
            <label for="price_per_night" class="form-label">Цена за ночь</label>
            <input type="number" name="price_per_night" id="price_per_night" class="form-control" step="0.01" value="{{ old('price_per_night') }}" required>
            @error('price_per_night')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Выбор тегов -->
        @if(isset($tags))
            @foreach($tags as $category => $tagsGroup)
                <div class="mb-3">
                    <label class="form-label">{{ $category }}</label>
                    <div>
                        @foreach($tagsGroup as $tag)
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="tags[]" id="tag{{ $tag->id }}" value="{{ $tag->id }}" class="form-check-input" 
                                    {{ (is_array(old('tags')) && in_array($tag->id, old('tags'))) ? 'checked' : '' }}>
                                <label for="tag{{ $tag->id }}" class="form-check-label">{{ $tag->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Загрузка изображений: можно выбрать до 12 файлов -->
        <div class="mb-3">
            <label for="images" class="form-label">Изображения жилья</label>
            <input type="file" name="images[]" id="images" class="form-control" multiple>
            <small class="form-text text-muted">
                Выберите от 1 до 12 файлов. Допустимые форматы: jpeg, png, jpg, gif, svg (максимум 2MB для каждого файла).
            </small>
            @error('images.*')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Добавить жильё</button>
    </form>
</div>
@endsection
