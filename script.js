
function showPreview(event) {
    const preview = document.getElementById('preview');
    const file = event.target.files[0];

    if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };

        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }
}

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
  
function showPreview(event) {
    const preview = document.getElementById('preview');
    const file = event.target.files[0];
    const acceptButton = document.getElementById('accept-button');

    if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';

            // Aktywuj przycisk
            acceptButton.classList.add('active');
            acceptButton.disabled = false;
        };

        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        preview.style.display = 'none';

        // Dezaktywuj przycisk
        acceptButton.classList.remove('active');
        acceptButton.disabled = true;
    }
}