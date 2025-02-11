@extends('layouts.main')

@section('title', 'Заявка на роль арендодателя')

@section('content')
<h1>Стать арендодателем</h1>

<p>Чтобы стать арендодателем, заполните форму ниже. Мы рассмотрим вашу заявку в ближайшее время.</p>

<form action="{{ route('landlord.apply.submit') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="message" class="form-label">Расскажите о себе и своём жилье</label>
        <textarea name="message" class="form-control" rows="5">{{ old('message') }}</textarea>
        @error('message') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Отправить заявку</button>
</form>
@endsection
