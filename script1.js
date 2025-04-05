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