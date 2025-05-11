@extends('layouts.main')
@section('title', 'Стать арендодателем')

@section('content')
<link rel="stylesheet" href="{{ url('/css/become_landlord.css') }}">

<div class="landlord-benefits-section">
    <div class="benefits-header">
        <h1 class="benefits-label">(О ВОЗМОЖНОСТЯХ)</h1>
        <h2 class="benefits-main-title">Станьте арендодателем и откройте новые горизонты дохода и свободы</h2>
    </div>
    <div class="benefits-cards">
        <div class="benefit-card-modern">
            <div class="benefit-title">Пассивный доход без лишних хлопот</div>
            <div class="benefit-desc">Сдавая жильё через нашу платформу, вы получаете стабильный доход, не отвлекаясь от своих дел. Мы берём на себя все организационные вопросы, чтобы вы могли наслаждаться свободным временем.</div>
            <div class="benefit-step">ШАГ 01 +</div>
        </div>
        <div class="benefit-card-modern">
            <div class="benefit-title">Надёжные арендаторы</div>
            <div class="benefit-desc">Мы тщательно проверяем каждого гостя, чтобы вы были уверены в безопасности своей недвижимости. Только проверенные арендаторы получают доступ к вашему жилью.</div>
            <div class="benefit-step">ШАГ 02 +</div>
        </div>
        <div class="benefit-card-modern">
            <div class="benefit-title">Простое управление</div>
            <div class="benefit-desc">Интуитивно понятный личный кабинет позволяет отслеживать бронирования, управлять ценами и получать отчёты в один клик. Всё для вашего удобства и контроля.</div>
            <div class="benefit-step">ШАГ 03 +</div>
        </div>
        <div class="benefit-card-modern">
            <div class="benefit-title">Поддержка 24/7 и максимальная заполняемость</div>
            <div class="benefit-desc">Наша команда всегда на связи и готова помочь в любой ситуации. Мы активно продвигаем ваши объекты, чтобы как можно больше гостей увидели ваше предложение.</div>
            <div class="benefit-step">ШАГ 04 +</div>
        </div>
    </div>
    <div class="benefits-extra-wrapper">
        <div class="benefits-extra-img">
            <img src="/images/landlord-benefit.jpg" alt="Вдохновляющее изображение" loading="lazy">
        </div>
        <div class="benefits-extra-text">
            <p>
                Присоединяйтесь к сообществу успешных арендодателей! Мы ценим доверие и стремимся сделать процесс сдачи жилья максимально простым и прозрачным. Ваша недвижимость — это не только стены и квадратные метры, но и источник новых возможностей, знакомств и стабильного дохода. Доверьтесь нашему опыту и сервису — и вы увидите, как легко можно получать выгоду от своего имущества.
            </p>
            <p class="benefits-extra-text-highlight">
                Начните свой путь к финансовой свободе и новым перспективам уже сегодня. Мы всегда рядом, чтобы поддержать вас на каждом этапе!
            </p>
        </div>
    </div>
</div>

<div class="landlord-form-block">
    <h1>Заявка на роль арендодателя</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <h5>Произошли следующие ошибки:</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('become-landlord.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label for="last_name">Фамилия</label>
                <input id="last_name" type="text"
                       class="@error('last_name') is-invalid @enderror"
                       name="last_name"
                       value="{{ old('last_name', $user->last_name ?? '') }}"
                       required autocomplete="family-name">
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="first_name">Имя</label>
                <input id="first_name" type="text"
                       class="@error('first_name') is-invalid @enderror"
                       name="first_name"
                       value="{{ old('first_name', $user->first_name ?? '') }}"
                       required autocomplete="given-name">
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="middle_name">Отчество</label>
            <input id="middle_name" type="text"
                   class="@error('middle_name') is-invalid @enderror"
                   name="middle_name"
                   value="{{ old('middle_name', $user->middle_name ?? '') }}"
                   required autocomplete="additional-name">
            @error('middle_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="passport_number">Номер паспорта</label>
            <input id="passport_number" type="text"
                   class="@error('passport_number') is-invalid @enderror"
                   name="passport_number"
                   value="{{ old('passport_number', $user->passport_number ?? '') }}"
                   placeholder="XXXX XXXXXX"
                   required pattern="\d{4} \d{6}"
                   title="Формат: 4 цифры, пробел, 6 цифр (например: 1234 567890)">
            @error('passport_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="passport_expiration_month">Месяц действия паспорта</label>
                <select id="passport_expiration_month" 
                        class="@error('passport_expiration_month') is-invalid @enderror"
                        name="passport_expiration_month"
                        required>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" 
                            {{ old('passport_expiration_month', $user->passport_expiration_date ? explode('/', $user->passport_expiration_date)[0] ?? '' : '') == $month ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                        </option>
                    @endforeach
                </select>
                @error('passport_expiration_month')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="passport_expiration_year">Год действия паспорта</label>
                <select id="passport_expiration_year" 
                        class="@error('passport_expiration_year') is-invalid @enderror"
                        name="passport_expiration_year"
                        required>
                    @foreach(range(date('y'), date('y') + 10) as $year)
                        <option value="{{ $year }}" 
                            {{ old('passport_expiration_year', $user->passport_expiration_date ? explode('/', $user->passport_expiration_date)[1] ?? '' : '') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
                @error('passport_expiration_year')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Отправить заявку</button>
    </form>
</div>
@endsection
