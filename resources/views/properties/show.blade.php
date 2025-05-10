<link rel="stylesheet" href="{{ url('/css/show_blade.css') }}">
@extends('layouts.main')

@section('title', $property->title)

@section('content')
    <div class="property-main-container property-main-simple wide-version">
        <div class="property-image-block property-image-simple wide-image">
            @if($property->images->count() > 1)
                <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($property->images as $index => $image)
                            <div class="carousel-item @if($index == 0) active @endif">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="property-main-image wide-img" alt="Изображение жилья">
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Предыдущий</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Следующий</span>
                    </button>
                </div>
            @elseif($property->images->count() == 1)
                <img src="{{ asset('storage/' . $property->images->first()->image_path) }}" class="property-main-image wide-img" alt="Изображение жилья">
            @else
                <img src="{{ asset('storage/default-placeholder.png') }}" class="property-main-image wide-img" alt="Нет изображения">
            @endif
        </div>
        <div class="property-info-block property-info-simple">
            <div class="property-info-content">
                <div class="property-header-actions">
                    @auth
                        @if(Auth::id() == $property->user_id)
                            <a href="{{ route('properties.edit', $property->id) }}" class="property-action-btn" title="Редактировать">
                                <span class="action-label">Редактировать</span>
                            </a>
                        @endif
                        @php
                            $favorites = Auth::user()->favorites;
                            $isFavorite = $favorites ? $favorites->contains('property_id', $property->id) : false;
                        @endphp
                        @if ($isFavorite)
                            <form action="{{ route('favorites.remove', $property->id) }}" method="POST" class="property-action-btn" title="Убрать из избранного">
                                @csrf
                                @method('DELETE')
                                <button  class="property-action-btn"><span class="action-label">В избранном</span></button>
                            </form>
                        @else
                            <form action="{{ route('favorites.add', $property->id) }}" method="POST" class="property-action-btn" title="Добавить в избранное">
                                @csrf
                                <button  class="property-action-btn"><span class="action-label">В избранное</span></button>
                            </form>
                        @endif
                    @endauth
                </div>
                <div class="property-category">Жильё</div>
                <h1 class="property-title">{{ $property->title }}</h1>
                <div class="property-rating-price">
                    <div class="property-rating">
                        <span class="property-rating-stars">
                            @php $averageRating = $property->reviews->count() > 0 ? round($property->reviews->avg('rating'), 1) : 0; @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="star @if($i <= $averageRating) filled @endif">&#9733;</span>
                            @endfor
                        </span>
                        <span class="property-reviews-count">{{ $property->reviews->count() }} отзывов</span>
                    </div>
                </div>
                <div class="property-price-main">{{ $property->price_per_night }} <span class="currency">₽</span></div>
                <div class="property-address"><strong>Адрес:</strong> {{ $property->address }}</div>
                <div class="property-description">{{ $property->description }}</div>
                @if($property->tags->isNotEmpty())
                    <div class="property-tags">
                        @foreach($property->tags as $tag)
                            <span class="property-tag">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
                <div class="property-booking-block property-booking-simple booking-col-style wide-booking-block">
                    @auth
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <form action="{{ route('bookings.store') }}" method="POST" class="booking-form-row wide-booking-form">
                            @csrf
                            <input type="hidden" name="property_id" value="{{ $property->id }}">
                            <div class="booking-date-group wide-booking-date-group">
                                <span class="booking-date-field">
                                    <span class="booking-date-label">c</span>
                                    <input type="date" name="start_date" id="start_date" class="booking-date-input" autocomplete="off" required placeholder="ДД.ММ.ГГГГ">
                                </span>
                                <span class="booking-date-sep">—</span>
                                <span class="booking-date-field">
                                    <span class="booking-date-label">по</span>
                                    <input type="date" name="end_date" id="end_date" class="booking-date-input" autocomplete="off" required placeholder="ДД.ММ.ГГГГ">
                                </span>
                            </div>
                            <button type="submit" class="booking-btn wide-booking-btn">Забронировать</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Табы -->
    <div class="property-tabs-block">
        <div class="property-tabs">
            <button class="property-tab active" id="tab-desc-btn" type="button">Описание</button>
        </div>
        <div class="property-tabs-content-row">
            <div class="property-tab-description property-tab-pane active" id="tab-desc">
                <p>Уютная квартира в самом сердце города, идеально подходящая как для краткосрочного, так и для длительного проживания. Современный ремонт, полностью меблированные комнаты и прекрасный вид из окна создают атмосферу домашнего уюта. В шаговой доступности находятся магазины, кафе и остановки общественного транспорта.</p>
                <div style="margin-top: 18px;">
                    <div style="color:#888; font-size:1.04rem; margin-bottom: 6px;">Характеристики:</div>
                    <ul class="property-desc-list">
                        <li>1 комната</li>
                        <li>1 спальня</li>
                        <li>1 ванная комната</li>
                        <li>1 кухня</li>
                    </ul>
                </div>
            </div>
            <aside class="property-tab-additional-info">
                <div class="property-additional-title">Дополнительная информация</div>
                <div class="property-additional-row">
                    <div class="property-additional-label">Размеры</div>
                    <div class="property-additional-value">3x3x3</div>
                </div>
                <div class="property-additional-row">
                    <div class="property-additional-label">Комнаты</div>
                    <div class="property-additional-value">4</div>
                </div>
            </aside>
        </div>
    </div>

    <div class="property-tabs-block">
        <div class="property-tabs">
            <button class="property-tab active" type="button" disabled>Отзывы ({{ $property->reviews->count() }})</button>
        </div>
        <div class="property-tabs-content-row">
            <div class="property-tab-reviews property-tab-pane active" id="tab-reviews-static">
                @if($property->reviews->count() > 0)
                    @php $shownReviews = 2; @endphp
                    @foreach($property->reviews as $i => $review)
                        <div class="review-card" style="@if($i >= $shownReviews) display:none; @endif">
                            <div class="review-text">{{ $review->comment }}</div>
                            <div class="review-author">{{ $review->user->name }}</div>
                            <div class="review-rating">
                                <span style="color:#b0b0b0; font-size:1.01rem; font-weight:400; margin-right:10px;">{{ $review->created_at->format('d.m.Y H:i') }}</span>
                                <span class="material-icons review-star">&#11088;</span>
                                <span style="font-weight:800; color:#1e2351; font-size:1.25rem;">{{ number_format($review->rating, 1) }}</span>
                            </div>
                        </div>
                    @endforeach
                    @if($property->reviews->count() > $shownReviews)
                        <button class="show-more-reviews-btn" onclick="
                            var cards = document.querySelectorAll('.review-card');
                            for(let i = $shownReviews; i < cards.length; i++) { cards[i].style.display = 'block'; }
                            this.style.display = 'none';
                        ">Показать ещё</button>
                    @endif
                @else
                    <p>Этот объект пока не имеет отзывов.</p>
                @endif
                @auth
                    @php
                        $hasCompletedBooking = \App\Models\Booking::where('property_id', $property->id)
                            ->where('user_id', Auth::id())
                            ->where('end_date', '<', now())
                            ->where('status', 'confirmed')
                            ->exists();
                        $alreadyReviewed = $property->reviews->where('user_id', Auth::id())->count() > 0;
                    @endphp
                    @if($hasCompletedBooking && !$alreadyReviewed)
                        <div class="property-add-review-block" style="margin-top:24px;">
                            <a href="{{ route('reviews.create', $property->id) }}" class="btn btn-secondary">Оставить отзыв</a>
                        </div>
                    @endif
                @endauth
            </div>
            <aside class="property-tab-additional-info"></aside>
        </div>
    </div>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Подключение Datepicker -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(function() {
            var propertyId = @json($property->id);
            var unavailableDates = [];
            function disableDates(date) {
                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                return [ unavailableDates.indexOf(string) == -1 ];
            }
            $.ajax({
                url: '/properties/' + propertyId + '/unavailable-dates',
                method: 'GET',
                success: function(dates) {
                    unavailableDates = dates;
                    $('#start_date, #end_date').datepicker({
                        dateFormat: 'yy-mm-dd',
                        minDate: 0,
                        beforeShowDay: disableDates
                    });
                }
            });
        });
    </script>
@endsection