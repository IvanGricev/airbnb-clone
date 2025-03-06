@extends('layouts.main')

@section('title', 'Оплата бронирования')

@section('content')
<h1>Оплата бронирования</h1>

<p><strong>Общая сумма:</strong> {{ $booking->total_price }} руб.</p>

<form action="{{ route('payments.process', $booking->id) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="card_number" class="form-label">Номер карты</label>
        <input type="text" name="card_number" id="card_number" class="form-control" placeholder="Введите номер карты" required maxlength="16">
        @error('card_number')
            <div class="text-danger">{{ $message }}</div>
        @enderror
        <!-- Блок для вывода информации о типе карты и ошибках валидации -->
        <div id="card_type_info" style="margin-top: 5px; font-weight: bold;"></div>
    </div>
    <div class="mb-3">
        <label for="expiration_date" class="form-label">Дата истечения (мм/гг)</label>
        <input type="text" name="expiration_date" id="expiration_date" class="form-control" placeholder="мм/гг" required>
        @error('expiration_date')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="cvv" class="form-label">CVV</label>
        <input type="text" name="cvv" id="cvv" class="form-control" placeholder="CVV" required maxlength="3">
        @error('cvv')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary">Оплатить</button>
</form>

<script>
// Вспомогательная функция для проверки номера карты по алгоритму Луна
function luhnCheck(number) {
    let sum = 0;
    let alt = false;
    for (let i = number.length - 1; i >= 0; i--) {
        let digit = parseInt(number[i], 10);
        if (alt) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        sum += digit;
        alt = !alt;
    }
    return (sum % 10) === 0;
}

// Вспомогательная функция для определения типа карты
function getCardType(number) {
    // Если номер начинается с "4" – Visa
    if (/^4/.test(number)) {
        return 'Visa';
    }
    // Если номер начинается с "51" - "55" – MasterCard
    if (/^5[1-5]/.test(number)) {
        return 'MasterCard';
    }
    // Если номер соответствует диапазону нового MasterCard: 2221-2720
    if (/^2(2[2-9]|[3-6]|7[0-1]|720)/.test(number)) {
        return 'MasterCard';
    }
    // Если номер начинается с "2200" - "2204" – МиР
    if (/^220[0-4]/.test(number)) {
        return 'Mir';
    }
    return 'Unknown';
}

// Обработчик событий ввода для поля с номером карты
document.getElementById('card_number').addEventListener('input', function() {
    let cardNumber = this.value.replace(/\s+/g, '');
    let info = document.getElementById('card_type_info');

    // Если введенные символы не являются цифрами
    if (!/^\d*$/.test(cardNumber)) {
        info.textContent = "Номер карты должен содержать только цифры.";
        info.style.color = "red";
        return;
    }

    // Если поле пустое – очищаем информацию
    if (cardNumber.length === 0) {
        info.textContent = "";
        return;
    }

    let type = getCardType(cardNumber);
    
    // Если длина номера равна 16, проверяем алгоритм Луна
    if (cardNumber.length === 16) {
        if (!luhnCheck(cardNumber)) {
            info.textContent = "Неверный номер карты (не проходит Luhn проверку).";
            info.style.color = "red";
            return;
        }
    }
    
    // Вывод типа карты в режиме реального времени
    info.textContent = "Тип карты: " + type;
    info.style.color = "green";
});
</script>
@endsection