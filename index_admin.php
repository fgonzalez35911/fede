<?php
session_start();
require '../conexion.php'; // Asegurate que la ruta a conexion sea correcta

// Lógica de Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Buscamos el usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    // Verificamos contraseña
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        header("Location: dashboard.php"); // Si entra bien, va al dashboard
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{background:#111;color:white;height:100vh;display:flex;align-items:center;justify-content:center;}</style>
</head>
<body>
    <div class="card bg-dark text-white p-4 shadow-lg" style="width:100%;max-width:400px;border:1px solid #333;">
        <div class="text-center mb-4">
             <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
             <h3>Acceso Seguro</h3>
        </div>
        
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <div class="input-group">
                    <span class="input-group-text bg-secondary border-0 text-white"><i class="fas fa-user"></i></span>
                    <input type="text" name="usuario" class="form-control bg-secondary text-white border-0" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-secondary border-0 text-white"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control bg-secondary text-white border-0" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">ENTRAR AL SISTEMA</button>
        </form>
        <div class="mt-4 text-center border-top border-secondary pt-3">
            <a href="../index.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left"></i> Volver a la Web Principal</a>
        </div>
    </div>
</body>
</html>