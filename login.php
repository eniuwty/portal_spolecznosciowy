<?php
session_start();
include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new SQLite3('db/database.sqlite');
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Przygotuj zapytanie, aby znaleźć użytkownika
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    // Sprawdź, czy użytkownik istnieje i czy hasło jest poprawne
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['avatar'] = $user['file_path'];
        header('Location: index.php');
        exit();
    } else {
        $loginError = "Nieprawidłowa nazwa użytkownika lub hasło.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-logo">Instagram</h1>
            <?php if (isset($loginError)): ?>
                <p class="error-message"><?php echo htmlspecialchars($loginError); ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Nazwa użytkownika" required>
                <input type="password" name="password" placeholder="Hasło" required>
                <input type="submit" value="Zaloguj się">
            </form>
            <p class="signup-link">Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
        </div>
    </div>

    <!-- Kontener na powiadomienie -->
    <div id="notification" class="notification hidden"></div>

    <script src="script2.js"></script>
    <?php if (isset($loginError)): ?>
    <script>
        showNotification("<?php echo htmlspecialchars($loginError); ?>", "error");
    </script>
    <?php endif; ?>
</body>
</html>