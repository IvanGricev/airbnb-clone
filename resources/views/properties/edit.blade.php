@extends('layouts.main')

@section('title', 'Редактирование жилья')

@section('content')
<div class="container mt-4">
    <h1>Редактирование жилья</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('properties.update', $property->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Название</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $property->title) }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Описание</label>
            <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description', $property->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Адрес</label>
            <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $property->address) }}" required>
        </div>

        <div class="mb-3">
            <label for="price_per_night" class="form-label">Цена за ночь</label>
            <input type="number" class="form-control" id="price_per_night" name="price_per_night" value="{{ old('price_per_night', $property->price_per_night) }}" required min="0">
        </div>

        <div class="mb-3">
            <label class="form-label">Теги</label>
            <div class="row">
                @foreach($tags as $category => $tagsGroup)
                    <div class="col-md-4 mb-2">
                        <h6>{{ $category }}</h6>
                        @foreach($tagsGroup as $tag)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag{{ $tag->id }}"
                                    {{ in_array($tag->id, old('tags', $property->tags->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tag{{ $tag->id }}">
                                    {{ $tag->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Текущие изображения</label>
            <div class="row">
                @foreach($property->images as $image)
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <img src="{{ $image->image_url }}" class="card-img-top" alt="{{ $property->title }}">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="delete_images[]" value="{{ $image->id }}" id="delete_image{{ $image->id }}">
                                    <label class="form-check-label" for="delete_image{{ $image->id }}">
                                        Удалить
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3">
            <label for="images" class="form-label">Добавить новые изображения</label>
            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml">
            <div class="form-text">Можно загрузить до 12 изображений. Поддерживаемые форматы: JPEG, PNG, JPG, GIF, SVG. Максимальный размер файла: 2MB.</div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            <a href="{{ route('properties.show', $property->id) }}" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('images');
    const deleteCheckboxes = document.querySelectorAll('input[name="delete_images[]"]');
    const maxImages = 12;

    function updateImageCount() {
        const currentImages = document.querySelectorAll('input[name="delete_images[]"]:not(:checked)').length;
        const newImages = imageInput.files.length;
        const totalImages = currentImages + newImages;

        if (totalImages > maxImages) {
            alert(`Общее количество изображений не может превышать ${maxImages}.`);
            imageInput.value = '';
        }
    }

    imageInput.addEventListener('change', updateImageCount);
    deleteCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const remainingImages = document.querySelectorAll('input[name="delete_images[]"]:not(:checked)').length;
            if (remainingImages === 0) {
                alert('Должно быть загружено хотя бы одно изображение.');
                this.checked = false;
            }
        });
    });
});
</script>
@endsection
