<?php
session_start();
unset($_SESSION['admin_user']);
session_write_close();
header('Location: login.php');
exit;


