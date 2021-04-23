<?php

session_start();

// If user is not logged in, redirect to the login page
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] === 0) {
    header("Location: ./index.php");
    exit();
}

session_destroy();
header("Location: ./index.php");
exit();

?>
