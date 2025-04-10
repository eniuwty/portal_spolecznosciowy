<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new SQLite3('db/database.sqlite');

$loginError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = 'uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = uniqid() . '.' . $fileExtension;
            $destinationPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destinationPath)) {
                // Usuń stare zdjęcie, jeśli istnieje
                $stmtOld = $db->prepare('SELECT file_path FROM users WHERE id = :user_id');
                $stmtOld->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
                $result = $stmtOld->execute();
                $oldFile = $result->fetchArray(SQLITE3_ASSOC)['file_path'];

                if ($oldFile && file_exists($oldFile) &&!file_exists("default1.jpg")) {
                    unlink($oldFile); // usuwa plik z dysku
                }

                // Zapisz nową ścieżkę
                $stmt = $db->prepare('UPDATE users SET file_path = :file_path WHERE id = :user_id');
                $stmt->bindValue(':file_path', $destinationPath, SQLITE3_TEXT);
                $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
                $stmt->execute();

              //  $_SESSION['avatar'] = $user['file_path'];

                $loginError = "Plik został przesłany i zaktualizowany pomyślnie.";
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

<!-- HTML + komunikaty -->
<link rel="stylesheet" href="style.css">
<div id="notification" class="notification hidden"></div>
<script src="script2.js"></script>

<?php if (isset($loginError)): ?>
<script>
  showNotification("<?php echo htmlspecialchars($loginError); ?>", "<?php echo $loginError === 'Plik został przesłany i zaktualizowany pomyślnie.' ? 'success' : 'error'; ?>");
</script>
<?php endif; ?>
<?php
header("Location: profile.php");
exit();
?>
