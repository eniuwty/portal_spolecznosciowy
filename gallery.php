<?php
session_start();
include 'navbar.php';

$db = new SQLite3('db/database.sqlite');
$query = '
    SELECT photos.file_path, photos.created_at, users.username 
    FROM photos 
    JOIN users ON photos.user_id = users.id
    ORDER BY photos.created_at DESC';
$result = $db->query($query);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="gallery-container">
        <h1>Galeria wszystkich zdjęć</h1>
        <div class="gallery-grid">
            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
                <div class="gallery-item">
                    <img src="<?php echo htmlspecialchars($row['file_path']); ?>" alt="Zdjęcie" class="gallery-img" loading="lazy">
                    <p class="gallery-username"><?php echo htmlspecialchars($row['username']); ?></p>
                    <p class="gallery-date"><?php echo htmlspecialchars($row['created_at']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>