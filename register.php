<?php
session_start();
include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new SQLite3('db/database.sqlite');
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sprawdź, czy użytkownik już istnieje
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $existingUser = $result->fetchArray(SQLITE3_ASSOC);

    if ($existingUser) {
        $registerError = "Nazwa użytkownika jest już zajęta.";
    } else {
        // Hashuj hasło
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        //ścieżka do avatara

        // Dodaj nowego użytkownika do bazy danych
        $stmt = $db->prepare('INSERT INTO users (username, password, file_path) VALUES (:username, :password, :file_path)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(':file_path','uploads/avatars/default1.jpg',SQLITE3_TEXT);
        $stmt->execute();

        header('Location: login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-logo">Instagram</h1>
            <?php if (isset($registerError)): ?>
                <p class="error-message"><?php echo htmlspecialchars($registerError); ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Nazwa użytkownika" required>
                <input type="password" name="password" placeholder="Hasło" required>
                <input type="submit" value="Zarejestruj się">
            </form>
            <p class="signup-link">Masz już konto? <a href="login.php">Zaloguj się</a></p>
        </div>
    </div>

    <!-- Kontener na powiadomienie -->
    <div id="notification" class="notification hidden"></div>

    <script src="script2.js"></script>
    <?php if (isset($registerError)): ?>
    <script>
        showNotification("<?php echo htmlspecialchars($registerError); ?>", "error");
    </script>
    <?php endif; ?>
</body>
</html>