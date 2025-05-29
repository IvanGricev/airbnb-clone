@foreach($reviews as $review)
    <div class="review-card">
        @if($review->property->images->isNotEmpty())
            <img src="{{ $review->property->images->first()->image_url }}" 
                 alt="{{ $review->property->title }}"
                 class="review-property-image"
                 onerror="this.onerror=null; this.src='{{ asset('images/no-image.jpg') }}'; this.alt='Нет изображения';">
        @else
            <div class="property-image-placeholder">
                <span class="placeholder-text">Нет изображения</span>
            </div>
        @endif
        <!-- ... остальной код ... -->
    </div>
@endforeach 