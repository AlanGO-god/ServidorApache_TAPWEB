<?php
require 'includes/auth_check.php';
require 'config/db.php';

$stmt = $pdo->query('SELECT id, nombre, email, fecha_registro FROM usuarios ORDER BY id DESC');
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Usuarios</title></head>
<body>
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['user_nombre']) ?> | <a href="logout.php">Cerrar sesión</a></h2>
    <a href="create.php">+ Nuevo usuario</a>
    <table border="1" cellpadding="8">
        <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Fecha registro</th><th>Acciones</th></tr>
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nombre']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['fecha_registro'] ?></td>
            <td>
                <a href="edit.php?id=<?= $u['id'] ?>">Editar</a> |
                <a href="delete.php?id=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>