<?php
session_start();
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    echo "<script>
        showNotification('{$notification['message']}', '{$notification['type']}');
    </script>";
    unset($_SESSION['notification']); // Usuń powiadomienie po wyświetleniu
}

$userId = $_SESSION['user_id'];

// Połączenie z bazą danych SQLite
$db = new SQLite3('db/database.sqlite');

// Pobierz listę użytkowników, których obserwuje aktualnie zalogowany użytkownik
$followedUsersQuery = $db->prepare("SELECT followed_id FROM follows WHERE follower_id = :follower_id");
$followedUsersQuery->bindValue(':follower_id', $userId, SQLITE3_INTEGER);
$followedUsersResult = $followedUsersQuery->execute();

$followedUsers = [];
while ($row = $followedUsersResult->fetchArray(SQLITE3_ASSOC)) {
    $followedUsers[] = $row['followed_id'];
}

// Jeśli użytkownik nie obserwuje nikogo, wyświetlimy odpowiedni komunikat
if (empty($followedUsers)) {
    echo "<p>Nie obserwujesz jeszcze żadnych użytkowników.</p>";
    exit();
}

// Pobierz zdjęcia tylko tych użytkowników, których obserwuje użytkownik
$placeholders = implode(',', array_fill(0, count($followedUsers), '?'));
$query = $db->prepare("SELECT id, file_path, user_id FROM photos WHERE user_id IN ($placeholders) ORDER BY created_at DESC");

// Dodajemy wartości do zapytania
foreach ($followedUsers as $index => $followedUserId) {
    $query->bindValue($index + 1, $followedUserId, SQLITE3_INTEGER);
}

$result = $query->execute();

// Funkcja do usuwania obserwacji
if (isset($_POST['unfollow_user_id'])) {
    $unfollowUserId = $_POST['unfollow_user_id'];
    $unfollowQuery = $db->prepare("DELETE FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
    $unfollowQuery->bindValue(':follower_id', $userId, SQLITE3_INTEGER);
    $unfollowQuery->bindValue(':followed_id', $unfollowUserId, SQLITE3_INTEGER);
    $unfollowQuery->execute();

    $_SESSION['notification'] = ['message' => 'Przestałeś obserwować użytkownika.', 'type' => 'success'];
    header('Location: index.php'); // Przekierowanie po odobserwowaniu
    exit();
}

// Funkcja do lajkowania zdjęć
if (isset($_POST['like_photo_id'])) {
    $photoId = $_POST['like_photo_id'];
    $likeQuery = $db->prepare("INSERT INTO likes (user_id, photo_id) VALUES (:user_id, :photo_id)");
    $likeQuery->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $likeQuery->bindValue(':photo_id', $photoId, SQLITE3_INTEGER);
    $likeQuery->execute();

    $_SESSION['notification'] = ['message' => 'Zdjęcie zostało polubione.', 'type' => 'success'];
    header('Location: index.php'); // Przekierowanie po polubieniu zdjęcia
    exit();
}

// Funkcja do usuwania lajka
if (isset($_POST['unlike_photo_id'])) {
    $photoId = $_POST['unlike_photo_id'];
    $unlikeQuery = $db->prepare("DELETE FROM likes WHERE user_id = :user_id AND photo_id = :photo_id");
    $unlikeQuery->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $unlikeQuery->bindValue(':photo_id', $photoId, SQLITE3_INTEGER);
    $unlikeQuery->execute();

    $_SESSION['notification'] = ['message' => 'Zdjęcie przestało być polubione.', 'type' => 'success'];
    header('Location: index.php'); // Przekierowanie po usunięciu lajka
    exit();
}

// Funkcja do dodawania komentarza
if (isset($_POST['comment_photo_id']) && isset($_POST['comment_text'])) {
    $photoId = $_POST['comment_photo_id'];
    $commentText = $_POST['comment_text'];

    // Sprawdź, czy komentarz nie jest pusty
    if (!empty($commentText)) {
        $commentQuery = $db->prepare("INSERT INTO comments (user_id, photo_id, comment_text) VALUES (:user_id, :photo_id, :comment_text)");
        $commentQuery->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $commentQuery->bindValue(':photo_id', $photoId, SQLITE3_INTEGER);
        $commentQuery->bindValue(':comment_text', $commentText, SQLITE3_TEXT);
        $commentQuery->execute();

        $_SESSION['notification'] = ['message' => 'Komentarz został dodany.', 'type' => 'success'];
        header('Location: index.php'); // Przekierowanie po dodaniu komentarza
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obserwowane zdjęcia</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            color: #fff;
            padding: 10px 10px;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .photo {
            margin-bottom: 20px;
        }
        .photo img {
            max-width: 100%;
            margin: 10px 0;
        }
        .photo-info {
            margin-top: 10px;
        }
        .photo-info button {
            font-size: 16px;
            padding: 5px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .photo-info button:hover {
            background-color: #0056b3;
        }
        .photo-info .unfollow {
            background-color: #f44336;
        }
        .photo-info .unfollow:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <header>
        <h1>Obserwowane zdjęcia</h1>
        <p>Witaj, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    </header>

    <div class="container">
        <h2>Zdjęcia osób, które obserwujesz</h2>

        <?php
        if ($result->numColumns() > 0) {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo '<div class="photo">';
                echo '<img src="' . htmlspecialchars($row['file_path']) . '" alt="Zdjęcie">';
                echo '<div class="photo-info">';

                // Lajkowanie zdjęcia lub usuwanie lajka
                $likeQuery = $db->prepare("SELECT * FROM likes WHERE user_id = :user_id AND photo_id = :photo_id");
                $likeQuery->bindValue(':user_id', $userId, SQLITE3_INTEGER);
                $likeQuery->bindValue(':photo_id', $row['id'], SQLITE3_INTEGER);
                $likeResult = $likeQuery->execute();

                if ($likeResult->fetchArray(SQLITE3_ASSOC)) {
                    echo '<form method="POST" style="display: inline;">';
                    echo '<input type="hidden" name="unlike_photo_id" value="' . $row['id'] . '">';
                    echo '<button type="submit">Usuń Lajka</button>';
                    echo '</form>';
                } else {
                    echo '<form method="POST" style="display: inline;">';
                    echo '<input type="hidden" name="like_photo_id" value="' . $row['id'] . '">';
                    echo '<button type="submit">Lubię to!</button>';
                    echo '</form>';
                }

                // Dodawanie komentarzy
                echo '<form method="POST" style="display: inline; margin-top: 10px;">';
                echo '<input type="hidden" name="comment_photo_id" value="' . $row['id'] . '">';
                echo '<textarea name="comment_text" placeholder="Dodaj komentarz" required></textarea>';
                echo '<button type="submit">Dodaj komentarz</button>';
                echo '</form>';

                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>Brak zdjęć do wyświetlenia.</p>';
        }
        ?>
        
        <h3>Obserwowane osoby</h3>
        <?php
        foreach ($followedUsers as $followedUserId) {
            $userQuery = $db->prepare("SELECT username FROM users WHERE id = :user_id");
            $userQuery->bindValue(':user_id', $followedUserId, SQLITE3_INTEGER);
            $userResult = $userQuery->execute();
            $user = $userResult->fetchArray(SQLITE3_ASSOC);
            
            echo '<div class="photo-info">';
            echo '<p>Obserwujesz: ' . htmlspecialchars($user['username']) . '</p>';
            echo '<form method="POST" style="display: inline;">';
            echo '<input type="hidden" name="unfollow_user_id" value="' . $followedUserId . '">';
            echo '<button type="submit" class="unfollow">Przestań obserwować</button>';
            echo '</form>';
            echo '</div>';
        }
        ?>
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
// Zamknięcie połączenia z bazą
$db->close();
?>
