@extends('layouts.main')

@section('title', 'Стать арендодателем')

@section('content')
<h1>Стать арендодателем и добавить жильё</h1>

<form action="{{ route('become-landlord.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="title" class="form-label">Название</label>
        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
        @error('description') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="address" class="form-label">Адрес</label>
        <input type="text" name="address" class="form-control" value="{{ old('address') }}" required>
        @error('address') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="price_per_night" class="form-label">Цена за ночь</label>
        <input type="number" name="price_per_night" class="form-control" value="{{ old('price_per_night') }}" min="0" required>
        @error('price_per_night') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Добавить жильё</button>
</form>
@endsection
