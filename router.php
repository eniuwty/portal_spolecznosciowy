<?php
//session_start();

// Pobierz aktualną ścieżkę URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Usuń kontekst katalogu, np. "/stronka/"
$basePath = '/stronka/'; // Dostosuj do katalogu aplikacji
$route = str_replace($basePath, '', $path);

// Obsługa przyjaznych linków
switch ($route) {
    case 'logowanie':
        include 'login.php';
        exit();

    case 'rejestracja':
        include 'register.php';
        exit();

    case 'strona':
        include 'index.php';
        exit();

    case 'galeria':
        include 'gallery.php';
        exit();
    
    case '':
        include 'index.php';
        exit();

    default:
        http_response_code(404);
        echo "Strona nie została znaleziona!";
        exit();
}
