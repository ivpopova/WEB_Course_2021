<?php
    session_start();
    date_default_timezone_set('Europe/Sofia');

    require_once "db/db.php";

    $db = new Database();

    function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    function isAdmin() {
        if (isLoggedIn()) {
            return ($_SESSION['user']['admin'] == "1");
        }

        return false;
    }
?>