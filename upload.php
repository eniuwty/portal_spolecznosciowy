<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Konfiguracja bazy danych
$db = new SQLite3('db/database.sqlite');

// Obsługa przesyłania pliku
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Dozwolone rozszerzenia plików
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Przenieś plik do katalogu "uploads"
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $destinationPath = $uploadDir . uniqid() . '.' . $fileExtension;

            if (move_uploaded_file($fileTmpPath, $destinationPath)) {
                // Zapisz informacje o pliku w bazie danych
                
                $stmt = $db->prepare('INSERT INTO photos (user_id, file_path) VALUES (:user_id, :file_path)');
                $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
                $stmt->bindValue(':file_path', $destinationPath, SQLITE3_TEXT);
              //  $stmt->bindValue(':upload_date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
                $stmt->execute();

                //$stmt = $db->prepare('INSERT INTO photos (user_id, filename, upload_date) VALUES (:user_id, :filename, :upload_date)');
                //$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
                //$stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
                //$stmt->execute();
                $loginError = "Plik został przesłany pomyślnie.";
            } else {
                $loginError = "Wystąpił błąd podczas przenoszenia pliku.";
            }
        } else {
            $loginError = "Nieprawidłowy typ pliku. Dozwolone są: jpg, jpeg, png, gif.";
        }
    } else {
        $loginError = "Wystąpił błąd podczas przesyłania pliku.";
    }
}

?>
<link rel="stylesheet" href="style.css">
<div id="notification" class="notification hidden"></div>

<script src="script2.js"></script>
<?php if (isset($loginError)): ?>
<script>
    showNotification("<?php echo htmlspecialchars($loginError); ?>", "error");
</script>
<?php endif; ?>
<?php
header("Location: index.php");
exit();
?>