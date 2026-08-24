<?php
require 'includes/auth_check.php';
require 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (strlen($nombre) < 2 || strlen($nombre) > 100) {
        $error = 'El nombre debe tener entre 2 y 100 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico no válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$nombre, $email, $hash]);
            header('Location: list.php');
            exit;
        } catch (PDOException $e) {
            $error = 'El correo ya está registrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Nuevo usuario</title></head>
<body>
    <h2>Nuevo usuario</h2>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" action="create.php">
        <label>Nombre: <input type="text" name="nombre" required></label><br><br>
        <label>Email: <input type="email" name="email" required></label><br><br>
        <label>Contraseña: <input type="password" name="password" required></label><br><br>
        <button type="submit">Guardar</button>
    </form>
    <a href="list.php">Volver</a>
</body>
</html>
