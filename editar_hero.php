<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit; }
require '../conexion.php';

// Actualizar Datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Guardar textos
    $updates = [
        'hero_titulo' => $_POST['titulo'],
        'hero_subtitulo' => $_POST['subtitulo']
    ];

    foreach ($updates as $clave => $valor) {
        $stmt = $pdo->prepare("INSERT INTO config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$clave, $valor, $valor]);
    }

    // Guardar Imagen si se subió nueva
    if (isset($_FILES['hero_img']) && $_FILES['hero_img']['error'] == 0) {
        $ext = pathinfo($_FILES['hero_img']['name'], PATHINFO_EXTENSION);
        $destino = "../assets/img/hero_bg." . $ext; // Sobreescribimos para ahorrar espacio
        move_uploaded_file($_FILES['hero_img']['tmp_name'], $destino);
        
        // Guardamos la ruta en BD
        $ruta_db = "assets/img/hero_bg." . $ext . "?v=" . time(); // Truco para romper caché
        $stmt = $pdo->prepare("INSERT INTO config (clave, valor) VALUES ('hero_imagen', ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$ruta_db, $ruta_db]);
    }
    $mensaje = "¡Hero actualizado correctamente!";
}

// Obtener datos actuales
$config_raw = $pdo->query("SELECT * FROM config")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Hero - FedeAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'menu.php'; ?>
    
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Editar Sección Principal (Hero)</h4>
            </div>
            <div class="card-body">
                <?php if(isset($mensaje)) echo "<div class='alert alert-success'>$mensaje</div>"; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Título Principal</label>
                        <input type="text" name="titulo" class="form-control" value="<?= $config_raw['hero_titulo'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subtítulo</label>
                        <input type="text" name="subtitulo" class="form-control" value="<?= $config_raw['hero_subtitulo'] ?? '' ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Imagen de Fondo Actual</label><br>
                        <?php if(isset($config_raw['hero_imagen'])): ?>
                            <img src="../<?= explode('?', $config_raw['hero_imagen'])[0] ?>" class="img-fluid rounded mb-2" style="max-height: 200px;">
                        <?php endif; ?>
                        <input type="file" name="hero_img" class="form-control">
                        <small class="text-muted">Sube una imagen horizontal (JPG/PNG/WEBP). Se optimizará automáticamente.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
