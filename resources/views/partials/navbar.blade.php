<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<nav class="navbar navbar-expand-lg navbar-transparent ">
    <div class="container-fluid container">
        <a class="navbar-brand" href="{{ route('home') }}">
        <img src="{{ asset('images/logo.svg') }}" alt="Airbnb Clone" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse p-2" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('bookings.history') }}">Мои бронирования</a></li>
                    
                    @if(Auth::user()->role === 'landlord')
                        <li class="nav-item"><a class="nav-link" href="{{ route('landlord.bookings') }}">Управление арендой</a></li>
                    @endif

                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Админка</a></li>
                    @endif

                    <li class="nav-item">
                        <div class="dropdown text-end">
                            <div class="user-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ asset('images/user-placeholder.svg') }}" alt="user" width="32" height="32" class="rounded-circle">
                            <span>{{ Auth::user()->name }}</span>
                            </div>
                            <ul class="dropdown-menu text-small">
                                <li><a class="dropdown-item" href="{{ route('user.profile') }}">Мой профиль</a></li>
                                <li><a class="dropdown-item" href="{{ route('chat.conversations') }}">Мои чаты</a></li>
                                @if(Auth::user()->role === 'landlord')
                                    <li><a class="dropdown-item" href="{{ route('properties.create') }}">Добавить жильё</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('become-landlord.form') }}">Стать арендодателем</a></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('support.create') }}">Обратиться в поддержку</a></li>                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-link dropdown-item">Выход</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endauth

                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Вход</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Регистрация</a></li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
