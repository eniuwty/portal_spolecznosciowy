<?php
session_start();
include "navbar.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    echo "dziala?";
}
?>