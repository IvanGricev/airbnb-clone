@extends('layouts.main')

@section('title', 'Оставить отзыв')

@section('content')
<h1>Оставить отзыв для "{{ $property->title }}"</h1>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form action="{{ route('reviews.store', $property->id) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="rating" class="form-label">Рейтинг</label>
        <select name="rating" class="form-select" required>
            <option value="">Выберите рейтинг</option>
            @for($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>
                    {{ $i }} {{ $i == 1 ? 'звезда' : 'звезды' }}
                </option>
            @endfor
        </select>
        @error('rating') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="comment" class="form-label">Комментарий</label>
        <textarea name="comment" class="form-control" rows="5">{{ old('comment') }}</textarea>
        @error('comment') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Отправить отзыв</button>
</form>
@endsection
