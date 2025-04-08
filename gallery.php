<?php
session_start();
include 'navbar.php';

$db = new SQLite3('db/database.sqlite');
$userId = $_SESSION['user_id'] ?? null;

// Obsługa lajków
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_photo_id'])) {
    $photoId = $_POST['like_photo_id'];
    if ($userId) {
        $check = $db->prepare("SELECT id FROM likes WHERE user_id = :user_id AND photo_id = :photo_id");
        $check->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $check->bindValue(':photo_id', $photoId, SQLITE3_INTEGER);
        $res = $check->execute()->fetchArray();
        if (!$res) {
            $stmt = $db->prepare("INSERT INTO likes (user_id, photo_id, created_at) VALUES (:user_id, :photo_id, datetime('now'))");
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $stmt->bindValue(':photo_id', $photoId, SQLITE3_INTEGER);
            $stmt->execute();
        }
    }
}

// Obsługa komentarzy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_photo_id'], $_POST['comment_text'])) {
    $photoId = $_POST['comment_photo_id'];
    $comment = trim($_POST['comment_text']);
    if ($userId && $comment !== '') {
        $stmt = $db->prepare("INSERT INTO comments (user_id, photo_id, content, created_at) VALUES (:user_id, :photo_id, :content, datetime('now'))");
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':photo_id', $photoId, SQLITE3_INTEGER);
        $stmt->bindValue(':content', $comment, SQLITE3_TEXT);
        $stmt->execute();
    }
}

// Obsługa obserwowania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow_user_id'])) {
    $followedUserId = $_POST['follow_user_id'];
    if ($userId) {
        // Sprawdź, czy już obserwujesz tego użytkownika
        $checkFollow = $db->prepare("SELECT id FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
        $checkFollow->bindValue(':follower_id', $userId, SQLITE3_INTEGER);
        $checkFollow->bindValue(':followed_id', $followedUserId, SQLITE3_INTEGER);
        $res = $checkFollow->execute()->fetchArray();
        
        if ($res) {
            // Jeśli obserwujesz, usuń obserwację
            $stmt = $db->prepare("DELETE FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
            $stmt->bindValue(':follower_id', $userId, SQLITE3_INTEGER);
            $stmt->bindValue(':followed_id', $followedUserId, SQLITE3_INTEGER);
            $stmt->execute();
        } else {
            // Jeśli nie obserwujesz, dodaj obserwację
            $stmt = $db->prepare("INSERT INTO follows (follower_id, followed_id, created_at) VALUES (:follower_id, :followed_id, datetime('now'))");
            $stmt->bindValue(':follower_id', $userId, SQLITE3_INTEGER);
            $stmt->bindValue(':followed_id', $followedUserId, SQLITE3_INTEGER);
            $stmt->execute();
        }
    }
}

// Pobranie wszystkich zdjęć i użytkowników
$query = '
    SELECT photos.id, photos.file_path, photos.created_at, users.id AS user_id, users.username 
    FROM photos 
    JOIN users ON photos.user_id = users.id
    ORDER BY photos.created_at DESC';
$result = $db->query($query);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Galeria</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .gallery-item { margin-bottom: 30px; border: 1px solid #ccc; padding: 10px; border-radius: 10px; }
        .comments { display: none; margin-top: 10px; }
        .toggle-comments { cursor: pointer; color: blue; text-decoration: underline; }
        .follow-btn { cursor: pointer; color: blue; text-decoration: underline; margin-top: 10px; }
        .unfollow-btn { cursor: pointer; color: red; text-decoration: underline; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="gallery-container">
        <h1>Galeria wszystkich zdjęć</h1>
        <div class="gallery-grid">
            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : 
                $photoId = $row['id'];
                $userIdInPhoto = $row['user_id'];

                // Sprawdzenie, czy obserwujesz danego użytkownika
                $followCheck = $db->prepare("SELECT id FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
                $followCheck->bindValue(':follower_id', $userId, SQLITE3_INTEGER);
                $followCheck->bindValue(':followed_id', $userIdInPhoto, SQLITE3_INTEGER);
                $followResult = $followCheck->execute()->fetchArray();
                $isFollowing = $followResult ? true : false;

                // Liczba lajków
                $likes = $db->querySingle("SELECT COUNT(*) FROM likes WHERE photo_id = $photoId");

                // Komentarze
                $commentsQuery = $db->prepare("SELECT c.content, c.created_at, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.photo_id = :pid ORDER BY c.created_at DESC");
                $commentsQuery->bindValue(':pid', $photoId, SQLITE3_INTEGER);
                $commentsResult = $commentsQuery->execute();
            ?>
                <div class="gallery-item">
                    <img src="<?php echo htmlspecialchars($row['file_path']); ?>" alt="Zdjęcie" class="gallery-img" loading="lazy">
                    <p><strong><?php echo htmlspecialchars($row['username']); ?></strong> | <?php echo $row['created_at']; ?></p>
                    
                    <form method="POST">
                        <input type="hidden" name="like_photo_id" value="<?php echo $photoId; ?>">
                        <button type="submit">❤️ Polub (<?php echo $likes; ?>)</button>
                    </form>

                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="comment_photo_id" value="<?php echo $photoId; ?>">
                        <textarea name="comment_text" placeholder="Dodaj komentarz..." required></textarea>
                        <button type="submit">Skomentuj</button>
                    </form>

                    <p class="toggle-comments" onclick="toggleComments(<?php echo $photoId; ?>)">Pokaż/ukryj komentarze</p>
                    <div class="comments" id="comments-<?php echo $photoId; ?>">
                        <?php while ($comment = $commentsResult->fetchArray(SQLITE3_ASSOC)) : ?>
                            <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?> <em>(<?php echo $comment['created_at']; ?>)</em></p>
                        <?php endwhile; ?>
                    </div>

                    <!-- Przycisk obserwowania -->
                    <?php if ($userId && $userId != $userIdInPhoto) : ?>
                        <?php if ($isFollowing) : ?>
                            <form method="POST">
                                <input type="hidden" name="follow_user_id" value="<?php echo $userIdInPhoto; ?>">
                                <button type="submit" class="unfollow-btn">🚫 Odobserwuj</button>
                            </form>
                        <?php else : ?>
                            <form method="POST">
                                <input type="hidden" name="follow_user_id" value="<?php echo $userIdInPhoto; ?>">
                                <button type="submit" class="follow-btn">👁️ Obserwuj</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function toggleComments(photoId) {
            const el = document.getElementById('comments-' + photoId);
            el.style.display = el.style.display === 'none' || el.style.display === '' ? 'block' : 'none';
        }
    </script>
</body>
</html>
