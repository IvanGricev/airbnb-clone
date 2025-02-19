<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid container">
        <a class="navbar-brand" href="{{ route('home') }}">Airbnb Clone</a>
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
                            <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="https://github.com/mdo.png" alt="mdo" width="32" height="32" class="rounded-circle">
                            </a>
                            <ul class="dropdown-menu text-small">
                                <li><a class="dropdown-item" href="#">Профиль</a></li>
                                @if(Auth::user()->role === 'landlord')
                                    <li><a class="dropdown-item" href="{{ route('properties.create') }}">Добавить жильё</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('become-landlord.form') }}">Стать арендодателем</a></li>
                                @endif
                                <li><a class="dropdown-item" href="#">Настройки</a></li>
                                <li><a class="dropdown-item" href="{{ route('support.tickets.index') }}">Мои запросы в поддержку</a></li>
                                <li><a class="dropdown-item" href="{{ route('support.tickets.create') }}">Обратиться в поддержку</a></li>
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
