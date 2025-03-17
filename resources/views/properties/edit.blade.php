@extends('layouts.main')

@section('title', 'Редактировать жильё')

@section('content')
<h1>Редактировать жильё</h1>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Форма редактирования -->
<form action="{{ route('properties.update', $property->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- Название -->
    <div class="mb-3">
        <label for="title" class="form-label">Название</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $property->title) }}" required>
        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <!-- Описание -->
    <div class="mb-3">
        <label for="description" class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="5" required>{{ old('description', $property->description) }}</textarea>
        @error('description') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <!-- Адрес -->
    <div class="mb-3">
        <label for="address" class="form-label">Адрес</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $property->address) }}" required>
        @error('address') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <!-- Цена за ночь -->
    <div class="mb-3">
        <label for="price_per_night" class="form-label">Цена за ночь (руб.)</label>
        <input type="number" name="price_per_night" class="form-control" step="0.01" value="{{ old('price_per_night', $property->price_per_night) }}" required>
        @error('price_per_night') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <!-- Выбор тегов по категориям -->
    @foreach($tags as $category => $tagsGroup)
        <div class="mb-3">
            <label class="form-label">{{ $category }}</label>
            <div>
                @foreach($tagsGroup as $tag)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="tags[]" id="tag{{$tag->id}}" value="{{ $tag->id }}"
                            {{ in_array($tag->id, old('tags', $property->tags->pluck('id')->toArray())) ? 'checked' : '' }}>
                        <label class="form-check-label" for="tag{{$tag->id}}">{{ $tag->name }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Загрузка новых изображений -->
    <div class="mb-3">
        <label for="images" class="form-label">Добавить изображения</label>
        <input type="file" name="images[]" id="images" class="form-control" multiple>
        <small class="form-text text-muted">
            Выберите от 1 до 12 файлов. Допустимые форматы: jpeg, png, jpg, gif, svg (максимум 2MB для каждого файла).
        </small>
        @error('images.*') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
</form>

<!-- Список текущих изображений -->
<h2 class="mt-5">Текущие изображения</h2>
<div class="row">
    @foreach($property->images as $image)
        <div class="col-md-3 text-center mb-3">
            <img src="{{ asset('storage/' . $image->image_path) }}" class="img-thumbnail mb-2" alt="Изображение">
            <form action="{{ route('properties.images.delete', $image->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
            </form>
        </div>
    @endforeach
</div>
@endsection
