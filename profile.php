<?php
session_start();
include "navbar.php";

// Sprawdzamy, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Przekierowanie do strony logowania, jeśli użytkownik nie jest zalogowany
    exit();
}

if (isset($_SESSION['upload_status'])) {
    $loginError = $_SESSION['upload_status'];
    unset($_SESSION['upload_status']); // czyścimy po pokazaniu
}


// Połączenie z bazą danych
$db = new SQLite3('db/database.sqlite');

// Pobierz dane użytkownika
$userId = $_SESSION['user_id'];  // Załóżmy, że ID użytkownika jest zapisane w sesji

// Pobierz liczbę obserwujących (follower_id w tabeli "follows")
$followersStmt = $db->prepare('SELECT COUNT(*) FROM follows WHERE followed_id = :user_id');
$followersStmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
$followersResult = $followersStmt->execute();
$followersCount = $followersResult->fetchArray(SQLITE3_ASSOC)['COUNT(*)'];

// Pobierz liczbę obserwowanych (follower_id w tabeli "follows")
$followingStmt = $db->prepare('SELECT COUNT(*) FROM follows WHERE follower_id = :user_id');
$followingStmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
$followingResult = $followingStmt->execute();
$followingCount = $followingResult->fetchArray(SQLITE3_ASSOC)['COUNT(*)'];

// Pobierz liczbę zdjęć (ilość zdjęć powiązanych z użytkownikiem w tabeli "photos")
$photosStmt = $db->prepare('SELECT COUNT(*) FROM photos WHERE user_id = :user_id');
$photosStmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
$photosResult = $photosStmt->execute();
$photosCount = $photosResult->fetchArray(SQLITE3_ASSOC)['COUNT(*)'];

// Pobierz sumę lajków (liczba lajków z tabeli "likes")
$likesStmt = $db->prepare('SELECT COUNT(*) FROM likes WHERE user_id = :user_id');
$likesStmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
$likesResult = $likesStmt->execute();
$likesCount = $likesResult->fetchArray(SQLITE3_ASSOC)['COUNT(*)'];

$userStmt = $db->prepare('SELECT created_at FROM users WHERE id = :user_id');
$userStmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
$userResult = $userStmt->execute();
$userData = $userResult->fetchArray(SQLITE3_ASSOC);
$createdAt = $userData['created_at'];
?>

<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profil użytkownika</title>
        <link rel="stylesheet" href="style.css">
    </head>
<body>
    <div class="stripe">
        <div class="page-container">


    <form action="upload_avatar.php" method="POST" enctype="multipart/form-data">
    <input type="file" id="fileInput" name="file" accept="image/*" style="display:none" onchange="showPreview_avatar(event)">
<div class="profile-container">
        <div class="profile-left">
            <div class="profile-left-relative">
                <div class="avatar-wrapper" onclick="document.getElementById('fileInput').click()">
                    <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="Avatar" class="avatar" id="current-avatar">
                <img id="preview" src="" alt="Podgląd avatara">
                </div>
            <button type="submit" id="accept-button" disabled>Zaakceptuj</button>
        </div>
        </form>
        <div class="profile-center">
            <!-- Nazwa użytkownika -->
             <h3>Dołączono od</h3>
            <h2><?php echo $createdAt; ?></h2>
            <h3>Nazwa użytkownika:</h3>
            <h1><?php echo htmlspecialchars($_SESSION['username']); ?></h1>

        </div>

    </div>

        <div class="profile-right">
            <!-- Statystyki -->
            <div class="stat-item">
                <strong>Obserwujący:</strong> <?php echo $followersCount; ?>
            </div>
            <div class="stat-item">
                <strong>Obserwujesz:</strong> <?php echo $followingCount; ?>
            </div>
            <div class="stat-item">
                <strong>Zdjęcia:</strong> <?php echo $photosCount; ?>
            </div>
            <div class="stat-item">
                <strong>Lajki:</strong> <?php echo $likesCount; ?>
            </div>
        </div>
    </div>
    </div>
    </div>

<!-- Kontener na powiadomienie -->
<div id="notification" class="notification hidden"></div>

<script src="script.js"></script>
<?php if (isset($loginError)): ?>
<script>
    showNotification("<?php echo htmlspecialchars($loginError); ?>", "error");
</script>
<?php endif; ?>
</body>
</html>
<?php
$db->close();
?>