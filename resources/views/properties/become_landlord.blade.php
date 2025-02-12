@extends('layouts.main')

@section('title', 'Стать арендодателем')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Заявка на роль арендодателя</div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <form method="POST" action="{{ route('become-landlord.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Имя</label>
                            <input id="first_name" type="text" 
                                   class="form-control @error('first_name') is-invalid @enderror" 
                                   name="first_name" 
                                   value="{{ old('first_name', Auth::user()->first_name) }}" 
                                   required>
                            
                            @error('first_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Отчество</label>
                            <input id="middle_name" type="text" 
                                   class="form-control @error('middle_name') is-invalid @enderror" 
                                   name="middle_name" 
                                   value="{{ old('middle_name', Auth::user()->middle_name) }}" 
                                   required>
                            
                            @error('middle_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Фамилия</label>
                            <input id="last_name" type="text" 
                                   class="form-control @error('last_name') is-invalid @enderror" 
                                   name="last_name" 
                                   value="{{ old('last_name', Auth::user()->last_name) }}" 
                                   required>
                            
                            @error('last_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="passport_number" class="form-label">Номер паспорта</label>
                            <input id="passport_number" type="text" 
                                   class="form-control @error('passport_number') is-invalid @enderror" 
                                   name="passport_number" 
                                   value="{{ old('passport_number', Auth::user()->passport_number) }}" 
                                   placeholder="XX XX XXXXXX X" 
                                   required>
                            
                            @error('passport_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="passport_expiration_date" class="form-label">Срок действия паспорта</label>
                            <input id="passport_expiration_date" type="date" 
                                   class="form-control @error('passport_expiration_date') is-invalid @enderror" 
                                   name="passport_expiration_date" 
                                   value="{{ old('passport_expiration_date', Auth::user()->passport_expiration_date ? Auth::user()->passport_expiration_date->format('Y-m-d') : null) }}" 
                                   required>
                            
                            @error('passport_expiration_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Отправить заявку
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection