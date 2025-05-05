<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-col">
                <div class="footer-logo">
                    <img src="{{ asset('images/logo.svg') }}" alt="Housing Hub">
                </div>
                <p class="footer-desc" >
                    Мы помогаем найти дом вашей мечты или сдать недвижимость в аренду быстро, надежно и просто.
                </p>
            </div>
            
            <div class="footer-links-wrapper">
                <div class="footer-links">
                    <ul>
                    <li><a href="#">Поиск жилья</a></li>
                    <li><a href="#">Популярные районы</a></li>
                    <li><a href="#">Новые объявления</a></li>
                    <li><a href="#">Советы арендаторам</a></li>
                    </ul>
                    <ul>
                    <li><a href="#">Разместить объявление</a></li>
                    <li><a href="#">Правила публикации</a></li>
                    <li><a href="#">Помощь арендодателям</a></li>
                    <li><a href="#">Тарифы и услуги</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    background:rgb(255, 255, 255);
    color: #fff;
    padding: 60px 0 30px;
    margin-top: 80px;
}

.footer-content {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.footer-logo img {
    height: 40px;
    margin-bottom: 20px;
}

.footer-desc {
    color: rgba(90, 90, 90, 0.7);
    font-size: 0.95rem;
    line-height: 1.6;
    max-width: 300px;
    text-align: left;
}

.footer-col ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-col ul li {
    margin-bottom: 12px;
}
.footer-col ul li a {
    color: rgba(0, 0, 0, 0.7);
}

.footer-links-wrapper {
    width: 100%;
    display: flex;
    justify-content: flex-stat;
    margin-top: 40px;
}

.footer-links {
    display: flex;
    gap: 80px;
}

.footer-links ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #232323;
    text-decoration: none;
    font-size: 1.0rem;
    transition: color 0.2s;
}

.footer-links a:hover {
    color: #ffb43a;
}
</style>
