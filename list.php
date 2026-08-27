<?php
require 'includes/auth_check.php';
require 'config/db.php';

$stmt = $pdo->query('SELECT id, nombre, email, fecha_registro FROM usuarios ORDER BY id DESC');
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand">MiPanel</span>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Bienvenido, <strong><?= htmlspecialchars($_SESSION['user_nombre']) ?></strong></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de Usuarios</h2>
            <a href="create.php" class="btn btn-success">+ Nuevo usuario</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Fecha registro</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['nombre']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= $u['fecha_registro'] ?></td>
                                <td class="text-end">
                                    <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Editar</a>
                                    <a href="delete.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>