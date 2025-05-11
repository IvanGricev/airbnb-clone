<link rel="stylesheet" href="{{ url('/css/main.css') }}">
@extends('layouts.main')

@section('title', 'Список жилья')

@section('content')
<div class="hero-main">
    <div class="hero-left">
        <h1>
            <span class="bold">Хороший вид</span> <span class="bold">Лучшее место</span><br>
            <span class="dreams">Ваши мечты</span> <span class="bold">Прямо здесь</span>
        </h1>
        <p class="desc">
            Всё, что вам нужно для поиска места для аренды, будет здесь.<br>
            Наши предложения сделаны из отборных и лучших вариантов, которые подходят для аренды вашей мечты
        </p>
        <form class="search-form" action="{{ route('properties.index') }}" method="GET">
            <span class="search-icon">
                <img src="{{ asset('images/search-location.svg') }}" alt="Поиск" />
            </span>
            <input type="text" name="query" class="form-control" placeholder="Что ищем?" value="{{ request('query') }}">
            <button type="submit" class="search-btn">
                Поиск <span class="arrow">&rarr;</span>
            </button>
        </form>
    </div>
    <div class="hero-right">
        <div class="main-image">
            <img src="{{ asset('images/path_to_hero_image.jpg')}}" alt="Жилое пространство">
            <div class="mini-cards">
                <div class="mini-card active">
                    <div class="mini-card-img">
                        <img src="{{ asset('images/path_to_image_1.jpg')}}" alt="Manhattan Style">
                    </div>
                    <div class="mini-card-content">
                        <h3>Манхэттенский стиль</h3>
                        <p>Стиль, который подчеркивает индивидуальность и уют</p>
                        <span class="rating">
                            <span class="star">&#9733;</span>
                            4.8 <span class="reviews">(400+ Review)</span>
                        </span>
                    </div>
                </div>
                <div class="mini-card">
                    <div class="mini-card-img">
                        <img src="{{ asset('images/path_to_image_2.jpg')}}" alt="New Future">
                    </div>
                    <div class="mini-card-content">
                        <h3>Новый будущий</h3>
                        <p>Стиль, который подчеркивает индивидуальность и уют</p>
                        <span class="rating">
                            <span class="star">&#9733;</span>
                            4.5 <span class="reviews">(320+ Review)</span>
                        </span>
                    </div>
                </div>
                <div class="mini-card">
                    <div class="mini-card-img">
                        <img src="{{ asset('images/path_to_image_3.jpg')}}" alt="Modern Loft">
                    </div>
                    <div class="mini-card-content">
                        <h3>Современный лофт</h3>
                        <p>Стиль, который подчеркивает индивидуальность и уют</p>
                        <span class="rating">
                            <span class="star">&#9733;</span>
                            4.7 <span class="reviews">(210+ Review)</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="slider-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>
</div>

<div class="reviews-link">Посмотреть Объекты</div>
<h1>Наши предложения</h1>

<form action="{{ route('properties.index') }}" method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-2 mb-4">
            <input type="number" name="min_price" class="form-control" placeholder="Мин. цена" value="{{ request('min_price') }}">
        </div>
        <div class="col-md-2 mb-4">
            <input type="number" name="max_price" class="form-control" placeholder="Макс. цена" value="{{ request('max_price') }}">
        </div>
        <div class="col-md-3 mb-4">
            <select name="sort_order" class="form-control">
                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>От дешевого к дорогому</option>
                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>От дорогого к дешевому</option>
            </select>
        </div>
        <div class="col-md-2 mb-4">
        <button type="submit" class="btn btn-primary">Найти</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @foreach($tags as $category => $tagsGroup)
                <div class="mb-3">
                    <label class="form-label">{{ $category }}</label>
                    <div>
                        @foreach($tagsGroup as $tag)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="tags[]" id="tag{{ $tag->id }}" value="{{ $tag->id }}" 
                                    {{ in_array($tag->id, $selectedTags) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tag{{ $tag->id }}">{{ $tag->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
</form>

@if($properties->isEmpty())
    <p>Жильё не найдено.</p>
@else
    <div class="row">
        @foreach($properties as $property)
            <div class="col-md-3">
                <div class="card mb-4">
                    <!-- Отображение изображения -->
                    @if($property->images->count() > 0)
                        <img src="{{ asset('storage/' . $property->images->first()->image_path) }}" class="card-img-top" alt="Превью">
                    @else
                        <img src="{{ asset('storage/default-placeholder.png') }}" class="card-img-top" alt="Нет изображения">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $property->title }}</h5>
                        <p>
                            @if($property->reviews->count() > 0)
                                Средний рейтинг: {{ $property->average_rating }} из 5
                            @else
                                Нет оценок
                            @endif
                        </p>
                        <p class="card-text">{{ Str::limit($property->description, 100) }}</p>
                        @if($property->tags->isNotEmpty())
                            <p>
                                @foreach($property->tags as $tag)
                                    <span class="badge bg-secondary">{{ $tag->name }}</span>
                                @endforeach
                            </p>
                        @endif
                        <p><strong>Цена за ночь:</strong> {{ $property->price_per_night }} руб.</p>
                        <a href="{{ route('properties.show', $property->id) }}" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Пагинация -->
    {{ $properties->links() }}

<section class="reviews-section">
  <div class="reviews-header">
    <div class="reviews-link">Посмотреть Отзывы</div>
    <h2>Что Говорят Наши Клиенты?</h2>
  </div>
  <div class="reviews-slider">
    <!-- Левая карточка -->
    <div class="review-slide">
      <img src="{{ asset('images/otz1.jpg')}}" class="review-img" alt="">
      <div class="review-bubble">
        <div class="review-title">Мой дом быстро сдали в аренду!</div>
        <div class="review-text">
        Я наконец смог быстро сдать свой дом через Housing Hub, который немедленно познакомил меня с людьми, которые хотели арендовать мой дом. Потрясающе!
        </div>
        <div class="review-footer">
          <div class="review-user">
          <span class="avatar-placeholder">КГ</span>
            <div>
              <div class="user-name">Кортни Генри</div>
              <div class="user-role">Арендодатель</div>
            </div>
          </div>
          <div class="review-rating">
            <span class="star">&#9733;</span> 4.3
          </div>
        </div>
      </div>
    </div>
    <!-- Центральная карточка -->
    <div class="review-slide active">
      <img src="{{ asset('images/otz2.jpg')}}" class="review-img" alt="">
      <div class="review-bubble">
        <div class="review-title">Лучший сайт для поиска дома!</div>
        <div class="review-text">
          Через этот сайт я смогла легко получить дом с типом и особенностями, которые я хочу, без сложного процесса.
        </div>
        <div class="review-footer">
          <div class="review-user">
                <span class="avatar-placeholder">ДР</span>
            <div>
              <div class="user-name">Диана Расселл</div>
              <div class="user-role">Клиент</div>
            </div>
          </div>
          <div class="review-rating">
            <span class="star">&#9733;</span> 4.6
          </div>
        </div>
      </div>
    </div>
    <!-- Правая карточка -->
    <div class="review-slide">
      <img src="{{ asset('images/otz3.jpg')}}" class="review-img" alt="">
      <div class="review-bubble">
        <div class="review-title">Через Housing Hub я смог арендовать дом для себя</div>
        <div class="review-text">
          Поиск информации о том, какой дом мы хотим мы хотели очень быстро. Прямо связались с арендодателем, чтобы спросить о деталях, очень полезно!
        </div>
        <div class="review-footer">
          <div class="review-user">
            <span class="avatar-placeholder">ЭГ</span>
            <div>
              <div class="user-name">Эстер Говард</div>
              <div class="user-role">Клиент</div>
            </div>
          </div>
          <div class="review-rating">
            <span class="star">&#9733;</span> 4.6
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Точки-слайдер -->
  <div class="reviews-dots">
    <span class="dot"></span>
    <span class="dot active"></span>
    <span class="dot"></span>
  </div>
</section>

<section class="property-promo">
  <div class="promo-left">
    <div class="promo-label">Готов К Аренде</div>
    <h2>Давайте Посмотрим!</h2>
    <p class="promo-desc">
      Дома, рекомендованные нашими партнерами, которые были отобраны специально для того, чтобы стать домом вашей мечты!
    </p>
    <div class="promo-details">
      <div class="promo-details-title">Детали</div>
      <div class="promo-details-list">
        <div class="promo-detail">
          <img src="{{ asset('images/bed 1.svg')}}" alt="">
          <span>4 Спальни</span>
        </div>
        <div class="promo-detail">
          <img src="{{ asset('images/bath 1.svg')}}" alt="">
          <span>2 Санузла</span>
        </div>
        <div class="promo-detail">
          <img src="{{ asset('images/car-garage 1.svg')}}" alt="">
          <span>1 Гараж</span>
        </div>
        <div class="promo-detail">
          <img src="{{ asset('images/stairs-with-handrail 1.svg')}}" alt="">
          <span>2 Этажа</span>
        </div>
      </div>
    </div>
    <div class="promo-owner">
      <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Арендатор">
      <div>
        <div class="promo-owner-role">Арендатор</div>
        <div class="promo-owner-name">Юлия Иванова</div>
      </div>
      <button class="promo-contact-btn">Связаться</button>
    </div>
  </div>
  <div class="promo-right">
    <div class="promo-browser">
      <div class="promo-browser-dots">
        <span class="dot red"></span>
        <span class="dot yellow"></span>
        <span class="dot green"></span>
      </div>
      <img src="{{ asset('images/house.jpg')}}" alt="Дом мечты">
    </div>
  </div>
</section>

@endif
@endsection
