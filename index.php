<?php
session_start();
header('Location: ' . (isset($_SESSION['user_id']) ? 'list.php' : 'login.php'));
exit;
?>