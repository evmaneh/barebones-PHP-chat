<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo 'not_logged_in';
    exit;
}

$username = $_SESSION['username'];

$banned_users = file_exists('secret/logs/banned_users.txt') ? file('secret/logs/banned_users.txt', FILE_IGNORE_NEW_LINES) : [];

if (in_array($username, $banned_users)) {
    echo 'muted';
} else {
    echo 'not_muted';
}
?>
