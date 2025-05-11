@extends('layouts.main')

@section('title', 'Профиль пользователя')

@section('content')
<link rel="stylesheet" href="{{ url('/css/profile.css') }}">

<div class="profile-container">
    <div class="profile-header">
        <h1 class="profile-title">{{ Auth::user()->name }}, добро пожаловать в ваш профиль!</h1>
        <div class="profile-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editNameEmailModal">
                <i class="fas fa-user-edit"></i> Редактировать профиль
            </button>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editPasswordModal">
                <i class="fas fa-key"></i> Изменить пароль
            </button>
        </div>
    </div>

    <!-- Модальное окно для редактирования имени и email -->
    <div class="modal fade" id="editNameEmailModal" tabindex="-1" aria-labelledby="editNameEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNameEmailModalLabel">Редактировать профиль</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <form action="{{ route('user.update.name-email') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ Auth::user()->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ Auth::user()->email }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Сохранить изменения</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для изменения пароля -->
    <div class="modal fade" id="editPasswordModal" tabindex="-1" aria-labelledby="editPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPasswordModalLabel">Изменить пароль</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <form action="{{ route('user.update.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Текущий пароль</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Новый пароль</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Подтвердите новый пароль</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Сохранить изменения</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="profile-sections">
        <!-- Бронирования -->
        <section class="profile-section">
            <h2 class="section-title">Мои бронирования</h2>
            @if($bookings->isEmpty())
                <div class="empty-state">
                    <p>У вас нет текущих бронирований.</p>
                </div>
            @else
                <div class="bookings-grid">
                    @foreach($bookings as $booking)
                        <div class="booking-card">
                            <div class="booking-info">
                                <h3 class="booking-title">
                                    <a href="{{ route('properties.show', $booking->property->id) }}">{{ $booking->property->title }}</a>
                                </h3>
                                <p class="booking-dates">
                                    <i class="far fa-calendar-alt"></i>
                                    {{ $booking->start_date }} - {{ $booking->end_date }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Избранные объекты -->
        <section class="profile-section">
            <h2 class="section-title">Избранные объекты</h2>
            @if($favorites->isEmpty())
                <div class="empty-state">
                    <p>У вас нет избранных объектов.</p>
                </div>
            @else
                <div class="favorites-grid">
                    @foreach($favorites as $favorite)
                        <div class="property-card">
                            @if($favorite->property->images->count() > 0)
                                <img src="{{ asset('storage/' . $favorite->property->images->first()->image_path) }}" 
                                     class="property-image" alt="{{ $favorite->property->title }}">
                            @else
                                <img src="{{ asset('storage/default-placeholder.png') }}" 
                                     class="property-image" alt="Нет изображения">
                            @endif
                            <div class="property-info">
                                <h3 class="property-title">{{ $favorite->property->title }}</h3>
                                <p class="property-description">{{ Str::limit($favorite->property->description, 100) }}</p>
                                <a href="{{ route('properties.show', $favorite->property->id) }}" class="btn btn-primary">Подробнее</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Ранее арендованное жильё -->
        <section class="profile-section">
            <h2 class="section-title">Ранее арендованное жильё</h2>
            @if($pastBookings->isEmpty())
                <div class="empty-state">
                    <p>У вас нет ранее завершённых аренд.</p>
                </div>
            @else
                <div class="past-bookings-grid">
                    @foreach($pastBookings as $booking)
                        <div class="property-card">
                            @if($booking->property->images->count() > 0)
                                <img src="{{ asset('storage/' . $booking->property->images->first()->image_path) }}" 
                                     class="property-image" alt="{{ $booking->property->title }}">
                            @else
                                <img src="{{ asset('storage/default-placeholder.png') }}" 
                                     class="property-image" alt="Нет изображения">
                            @endif
                            <div class="property-info">
                                <h3 class="property-title">{{ $booking->property->title }}</h3>
                                <p class="property-description">{{ Str::limit($booking->property->description, 100) }}</p>
                                <a href="{{ route('properties.show', $booking->property->id) }}" class="btn btn-primary">Подробнее</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="view-all-link">
                    <a href="{{ route('bookings.history') }}" class="btn btn-link">Посмотреть все бронирования</a>
                </div>
            @endif
        </section>

        <!-- Часть арендодателя -->
        @if($user->role === 'landlord')
            <section class="profile-section landlord-section">
                <h2 class="section-title">Статистика арендодателя</h2>
                <div class="landlord-stats">
                    <div class="stat-card">
                        <h3>Общее количество бронирований</h3>
                        <p class="stat-value">{{ $totalBookings }}</p>
                    </div>
                    <div class="stat-card">
                        <h3>Общий доход</h3>
                        <p class="stat-value">{{ $totalRevenue }} руб.</p>
                    </div>
                </div>

                <h2 class="section-title">Мои объекты</h2>
                <div class="properties-grid">
                    @foreach($propertyBookings as $item)
                        <div class="property-card">
                            @if($item['property']->images->count() > 0)
                                <img src="{{ asset('storage/' . $item['property']->images->first()->image_path) }}" 
                                     class="property-image" alt="{{ $item['property']->title }}">
                            @else
                                <img src="{{ asset('storage/default-placeholder.png') }}" 
                                     class="property-image" alt="Нет изображения">
                            @endif
                            <div class="property-info">
                                <h3 class="property-title">{{ $item['property']->title }}</h3>
                                <div class="property-stats">
                                    <p><strong>Бронирований:</strong> {{ $item['booking_count'] }}</p>
                                    <p><strong>Доход:</strong> {{ $item['revenue'] }} руб.</p>
                                </div>
                                <div class="property-actions">
                                    <a href="{{ route('properties.show', $item['property']->id) }}" class="btn btn-primary">Подробнее</a>
                                    <a href="{{ route('properties.edit', $item['property']->id) }}" class="btn btn-secondary">Редактировать</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
