@extends('layouts.main')

@section('title', 'Административная панель')
@section('content')
    <h1>Административная панель</h1>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">Меню</div>
                <div class="card-body">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.landlord.applications') ? 'active' : '' }}" href="{{ route('admin.landlord.applications') }}">Заявки арендодателей</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.database.index') ? 'active' : '' }}" href="{{ route('admin.database.index') }}">Управление БД</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.support.index') ? 'active' : '' }}" href="{{ route('admin.support.index') }}">Тикеты поддержки</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            @yield('admin-content')
        </div>
    </div>
@endsection