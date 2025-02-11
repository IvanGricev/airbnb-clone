@extends('layouts.main')

@section('title', 'Добавить жильё')

@section('content')
<h1>Добавить жильё</h1>
<form action="{{ route('properties.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="title" class="form-label">Название</label>
        <input type="text" name="title" class="form-control" value="{{ old('title') }}">
        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Описание</label>
        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        @error('description') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="address" class="form-label">Адрес</label>
        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
        @error('address') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="price_per_night" class="form-label">Цена за ночь</label>
        <input type="number" name="price_per_night" class="form-control" value="{{ old('price_per_night') }}">
        @error('price_per_night') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <!-- Здесь можно добавить загрузку изображений -->
    <button type="submit" class="btn btn-primary">Добавить</button>
</form>
@endsection
