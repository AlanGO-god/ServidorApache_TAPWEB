<?php
require 'includes/auth_check.php';
require 'config/db.php';

$id = $_GET['id'] ?? null;
if ($id && filter_var($id, FILTER_VALIDATE_INT)) {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: list.php');
exit;