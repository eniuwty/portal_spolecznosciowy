<?php
session_start();
include 'navbar.php';

session_destroy();
header('Location: login.php');
exit;
?>
