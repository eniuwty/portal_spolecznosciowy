
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

// Funkcja wyświetlająca toast
function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    
    // Dodanie klasy "show" rozpoczynającej animację
    toast.classList.add('show');
    
    // Po 3 sekundach, ukryj powiadomienie
    setTimeout(() => {
      toast.classList.remove('show');
      toast.classList.add('hide');
    }, 3000); // Czas wyświetlania powiadomienia
  }
  