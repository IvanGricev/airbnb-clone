<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">Airbnb Clone</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @auth
                    <!-- Ссылки для всех авторизованных пользователей -->
                    <li class="nav-item"><a class="nav-link" href="{{ route('bookings.history') }}">Мои бронирования</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('support.tickets') }}">Мои тикеты</a></li>

                    <!-- Проверка роли администратора -->
                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Админка</a></li>
                    @endif

                    <!-- Проверка роли арендодателя -->
                    @if(Auth::user()->role === 'landlord')
                        <li class="nav-item"><a class="nav-link" href="{{ route('properties.create') }}">Добавить жильё</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('become-landlord.form') }}">Стать арендодателем</a></li>
                    @endif

                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-link nav-link">Выход</button>
                        </form>
                    </li>
                @endauth

                @guest
                    <!-- Ссылки для гостей -->
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Вход</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Регистрация</a></li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
