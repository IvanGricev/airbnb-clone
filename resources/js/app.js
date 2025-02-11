import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    //скрипт для обновления чата каждые 5 секунд
    const chatMessages = document.getElementById('chat-messages');

    if (chatMessages) {
        setInterval(() => {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMessages = doc.getElementById('chat-messages').innerHTML;
                    chatMessages.innerHTML = newMessages;
                });
        }, 5000);
    }
});
