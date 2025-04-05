<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoId = $_POST['photo_id'];

    $db = new SQLite3('db/database.sqlite');
    
    // Pobierz ścieżkę pliku
    $stmt = $db->prepare('SELECT file_path FROM photos WHERE id = :id AND user_id = :user_id');
    $stmt->bindValue(':id', $photoId, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($row) {
        $filePath = $row['file_path'];

        // Usuń rekord z bazy danych
        $stmt = $db->prepare('DELETE FROM photos WHERE id = :id AND user_id = :user_id');
        $stmt->bindValue(':id', $photoId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $stmt->execute();

        // Opcjonalnie: Usuń plik z systemu plików
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Ustaw komunikat o sukcesie
        $_SESSION['notification'] = [
            'message' => 'Zdjęcie zostało usunięte.',
            'type' => 'success'
        ];
    } else {
        // Ustaw komunikat o błędzie
        $_SESSION['notification'] = [
            'message' => 'Zdjęcie nie istnieje lub nie masz uprawnień do jego usunięcia.',
            'type' => 'error'
        ];
    }

    // Przekierowanie z powrotem na stronę główną
    header("Location: index.php");
    exit();
}
?>
