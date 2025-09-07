<?php
session_start();
if (!empty($_SESSION['ua_user'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;


