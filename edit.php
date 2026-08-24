<?php
require 'includes/auth_check.php';
require 'config/db.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    header('Location: list.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nombre) || empty($email)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (strlen($nombre) < 2 || strlen($nombre) > 100) {
        $error = 'El nombre debe tener entre 2 y 100 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico no válido.';
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?');
        $stmt->execute([$nombre, $email, $id]);
        header('Location: list.php');
        exit;
    }
}

$stmt = $pdo->prepare('SELECT id, nombre, email FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Editar usuario</title></head>
<body>
    <h2>Editar usuario</h2>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" action="edit.php?id=<?= $usuario['id'] ?>">
        <label>Nombre: <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required></label><br><br>
        <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required></label><br><br>
        <button type="submit">Actualizar</button>
    </form>
    <a href="list.php">Volver</a>
</body>
</html>