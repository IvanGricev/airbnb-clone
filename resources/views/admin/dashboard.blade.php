@extends('layouts.main')

@section('title', 'Административная панель')

@section('content')
<link rel="stylesheet" href="{{ url('/css/dashboard.admin.css') }}">

<div class="admin-dashboard">
    <div class="admin-header">
        <h1>Административная панель</h1>
    </div>
    
    <div class="admin-menu">
        <a href="{{ route('admin.landlord.applications') }}" class="admin-menu-item {{ request()->routeIs('admin.landlord.applications') ? 'active' : '' }}">
            <span class="menu-icon">🏠</span>
            <div class="menu-content">
                <div class="menu-title">Заявки арендодателей</div>
                <p class="menu-description">Управление заявками на размещение объектов</p>
            </div>
        </a>

        <a href="{{ route('admin.database.index') }}" class="admin-menu-item {{ request()->routeIs('admin.database.index') ? 'active' : '' }}">
            <span class="menu-icon">⭐</span>
            <div class="menu-content">
                <div class="menu-title">Управление БД</div>
                <p class="menu-description">Управление данными и настройками системы</p>
            </div>
        </a>

        <a href="{{ route('admin.support.index') }}" class="admin-menu-item {{ request()->routeIs('admin.support.index') ? 'active' : '' }}">
            <span class="menu-icon">💬</span>
            <div class="menu-content">
                <div class="menu-title">Тикеты поддержки</div>
                <p class="menu-description">Обработка обращений пользователей</p>
            </div>
        </a>
    </div>

    @yield('admin-content')
</div>
@endsection