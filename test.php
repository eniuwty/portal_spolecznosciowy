<?php
if (isset($_SESSION['user_id'])) {
    session_destroy();
}
session_start();
include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new SQLite3('db/database.sqlite');
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['username'] = $result['username'];
        header('Location: index.php');
        exit();
    } else {
        // Przekaż komunikat do powiadomienia
        $loginError = "Nieprawidłowa nazwa użytkownika lub hasło.";
    }
}
?>
<link rel="stylesheet" href="style.css">
<div class="login-container">
    <h1>Logowanie</h1>
    <form method="POST" class="login-form">
        <input type="text" name="username" placeholder="Nazwa użytkownika" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <button type="submit">Zaloguj</button>
    </form>
</div>

<!-- Kontener na powiadomienie -->
<div id="notification" class="notification hidden"></div>

<script src="script2.js"></script>
<?php if (isset($loginError)): ?>
<script>
    showNotification("<?php echo htmlspecialchars($loginError); ?>", "error");
</script>
<?php endif; ?>
