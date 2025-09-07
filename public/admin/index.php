<?php
session_start();
if (!empty($_SESSION['admin_user'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;


