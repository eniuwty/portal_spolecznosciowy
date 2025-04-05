function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    notification.textContent = message;

    // Ustawienie klasy w zależności od typu (sukces/błąd)
    notification.className = `notification ${type}`;

    // Pokaż powiadomienie
    notification.classList.remove('hidden');

    // Ukryj powiadomienie po 3 sekundach
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 3000);
}
