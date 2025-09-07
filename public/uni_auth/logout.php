<?php
session_start();
unset($_SESSION['ua_user']);
session_write_close();
header('Location: login.php');
exit;


