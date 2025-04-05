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


// Pobierz aktualną ścieżkę URL

?>
<!DOCTYPE html>
<html lang="pl">
<head>
<link rel="stylesheet" href="style.css">

    <script src="script.js"></script> <!-- Załadowanie skryptu -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strona główna</title>
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
        form {
            margin-top: 20px;
        }
        input[type="file"], input[type="submit"] {
            display: block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Witaj na stronie głównej!</h1>
        <p>Witaj, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    </header>
    <div class="container">
        <h2>Dodaj zdjęcie</h2>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <label for="file">Wybierz zdjęcie:</label>
        <input type="file" name="file" id="file" accept="image/*" onchange="showPreview(event)" required>
        <br>
        <img id="preview" src="" alt="Podgląd zdjęcia" style="display: none; max-width: 100%; margin-top: 10px;">
        <br>
        <input type="submit" value="Dodaj zdjęcie">
    </form>

        <h2>Twoje zdjęcia</h2>
        <div>
        <?php

            $db = new SQLite3('db/database.sqlite');
            // Pobierz zdjęcia użytkownika, w tym kolumnę 'id'
            $stmt = $db->prepare('SELECT id, file_path FROM photos WHERE user_id = :user_id');
            $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
            $result = $stmt->execute();

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo '<div style="margin-bottom: 20px;">';
                echo '<img src="' . htmlspecialchars($row['file_path']) . '" alt="Zdjęcie" style="max-width: 100%; margin: 10px 0;">';
                // Formularz z większym przyciskiem i nową linią
                echo '<form method="POST" action="delete_photo.php" style="display: block;">';
                echo '<input type="hidden" name="photo_id" value="' . htmlspecialchars($row['id']) . '">';
                echo '<button type="submit" style="margin-top: 10px; font-size: 18px; padding: 10px 20px; background-color: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer;">Usuń</button>';
                echo '</form>';
                echo '</div>';
            }
        ?>
        </div>
    </div>
</body>
</html>
<script src="script2.js"></script>
