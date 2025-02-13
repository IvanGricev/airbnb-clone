@extends('layouts.main')
@section('title', 'Стать арендодателем')

@section('content')
<div class="container mt-4">
    <h1>Заявка на роль арендодателя</h1>
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('become-landlord.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="last_name" class="form-label">Фамилия</label>
                    <input id="last_name" type="text"
                           class="form-control @error('last_name') is-invalid @enderror"
                           name="last_name"
                           value="{{ old('last_name', $user->last_name ?? '') }}"
                           required
                           autocomplete="family-name">
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="first_name" class="form-label">Имя</label>
                    <input id="first_name" type="text"
                           class="form-control @error('first_name') is-invalid @enderror"
                           name="first_name"
                           value="{{ old('first_name', $user->first_name ?? '') }}"
                           required
                           autocomplete="given-name">
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="middle_name" class="form-label">Отчество</label>
                    <input id="middle_name" type="text"
                           class="form-control @error('middle_name') is-invalid @enderror"
                           name="middle_name"
                           value="{{ old('middle_name', $user->middle_name ?? '') }}"
                           required
                           autocomplete="additional-name">
                    @error('middle_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="passport_number" class="form-label">Номер паспорта</label>
                    <input id="passport_number" type="text"
                           class="form-control @error('passport_number') is-invalid @enderror"
                           name="passport_number"
                           value="{{ old('passport_number', $user->passport_number ?? '') }}"
                           placeholder="XXXX XXXXXX"
                           required
                           pattern="\d{4} \d{6}"
                           title="Формат: 4 цифры, пробел, 6 цифр (например: 1234 567890)">
                    @error('passport_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="passport_expiration_month" class="form-label">Месяц действия паспорта</label>
                    <select id="passport_expiration_month" 
                            class="form-control @error('passport_expiration_month') is-invalid @enderror"
                            name="passport_expiration_month"
                            required>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" {{ old('passport_expiration_month', $user->passport_expiration_month ?? '') == $month ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                    @error('passport_expiration_month')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="passport_expiration_year" class="form-label">Год действия паспорта</label>
                    <select id="passport_expiration_year" 
                            class="form-control @error('passport_expiration_year') is-invalid @enderror"
                            name="passport_expiration_year"
                            required>
                        @foreach(range(date('Y'), date('Y') + 10) as $year)
                            <option value="{{ $year }}" {{ old('passport_expiration_year', $user->passport_expiration_year ?? '') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                    @error('passport_expiration_year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Отправить заявку</button>
            </form>
        </div>
    </div>
</div>
@endsection