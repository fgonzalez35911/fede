<?php
session_start();
require '../conexion.php';

// Lógica de Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuario o clave incorrecta";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Admin - FedeHome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#111;color:white;height:100vh;display:flex;align-items:center;justify-content:center;}</style>
</head>
<body>
    <div class="card bg-dark text-white p-4" style="width:100%;max-width:400px;border:1px solid #333;">
        <h3 class="text-center mb-4"><i class="fas fa-lock text-primary"></i> Acceso Seguro</h3>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Usuario</label>
                <input type="text" name="usuario" class="form-control bg-secondary text-white border-0" required>
            </div>
            <div class="mb-3">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control bg-secondary text-white border-0" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Entrar al Sistema</button>
        </form>
        <div class="mt-3 text-center">
            <small class="text-muted">Si es tu primera vez, <a href="instalar_usuario.php">crea tu usuario aquí</a>.</small>
        </div>
    </div>
</body>
</html>
