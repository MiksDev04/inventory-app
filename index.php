<?php

require "./includes/config.php";

// Connect to the database
session_start();

$URI = $_SERVER['REQUEST_URI'];

if (isLogIn()) {
    header("Location: ./dashboard/index.php");
    exit;
} else {
    header("Location: ./auth/login.php");
    exit;
}