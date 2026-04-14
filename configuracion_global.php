<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit; }
require '../conexion.php';

// --- PROCESAR GUARDADO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Guardar Textos
    foreach ($_POST as $clave => $valor) {
        if (strpos($clave, 'img_') === false) { 
            // Insertar o Actualizar (ON DUPLICATE KEY UPDATE)
            $stmt = $pdo->prepare("INSERT INTO config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
            $stmt->execute([$clave, $valor, $valor]);
        }
    }

    // 2. Guardar Imágenes
    foreach ($_FILES as $clave_form => $archivo) {
        if ($archivo['error'] == 0) {
            $clave_db = str_replace('file_', '', $clave_form); // Quitamos el prefijo file_
            
            // Nombre único para evitar caché
            $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombre_archivo = $clave_db . '_' . time() . '.' . $ext;
            $destino = "../assets/img/uploads/" . $nombre_archivo;
            
            if (!file_exists("../assets/img/uploads/")) mkdir("../assets/img/uploads/", 0777, true);
            
            if (move_uploaded_file($archivo['tmp_name'], $destino)) {
                $ruta_db = "assets/img/uploads/" . $nombre_archivo;
                $stmt = $pdo->prepare("INSERT INTO config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
                $stmt->execute([$clave_db, $ruta_db, $ruta_db]);
            }
        }
    }
    $mensaje = "¡Sitio actualizado correctamente!";
}

// --- LEER DATOS ACTUALES ---
$datos = $pdo->query("SELECT clave, valor FROM config")->fetchAll(PDO::FETCH_KEY_PAIR);

// Función auxiliar para no repetir código en el HTML
function renderCampo($titulo, $clave, $datos, $es_textarea = false) {
    $valor = $datos[$clave] ?? '';
    echo '<div class="mb-3">';
    echo '<label class="form-label fw-bold small text-uppercase text-primary">' . $titulo . '</label>';
    if ($es_textarea) {
        echo '<textarea name="' . $clave . '" class="form-control" rows="3">' . htmlspecialchars($valor) . '</textarea>';
    } else {
        echo '<input type="text" name="' . $clave . '" class="form-control" value="' . htmlspecialchars($valor) . '">';
    }
    echo '</div>';
}

function renderImagen($titulo, $clave, $datos) {
    $valor = $datos[$clave] ?? '';
    echo '<div class="mb-3 p-3 border rounded bg-white">';
    echo '<label class="form-label fw-bold small text-uppercase text-success mb-2"><i class="fas fa-image"></i> ' . $titulo . '</label>';
    echo '<div class="d-flex align-items-center">';
    if ($valor) {
        echo '<img src="../' . $valor . '" style="width: 60px; height: 60px; object-fit: cover; margin-right: 15px;" class="rounded border">';
    }
    echo '<input type="file" name="file_' . $clave . '" class="form-control form-control-sm">';
    echo '</div></div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Global - FedeAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light pb-5">
    
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-terminal me-2"></i>FedeAdmin</a>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Gestor Proyectos</a>
                <a href="../index.php" target="_blank" class="btn btn-primary btn-sm">Ver Web</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-sliders-h text-primary"></i> Configuración Global</h2>
            <?php if(isset($mensaje)) echo "<span class='badge bg-success p-2 fs-6'>$mensaje</span>"; ?>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                
                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white">SECCIÓN HERO (Inicio)</div>
                        <div class="card-body">
                            <?php renderImagen('Imagen de Fondo', 'hero_imagen', $datos); ?>
                            <?php renderCampo('Título Principal', 'hero_titulo', $datos); ?>
                            <?php renderCampo('Subtítulo', 'hero_subtitulo', $datos, true); ?>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-secondary text-white">SECCIÓN PORTFOLIO (Títulos)</div>
                        <div class="card-body">
                            <?php renderCampo('Título Sección', 'portfolio_titulo', $datos); ?>
                            <?php renderCampo('Bajada / Descripción', 'portfolio_subtitulo', $datos); ?>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-dark">SECCIÓN LIBROS (Writing)</div>
                        <div class="card-body">
                            <?php renderImagen('Portada del Libro', 'libro_imagen', $datos); ?>
                            <?php renderCampo('Título Libro', 'libro_titulo_1', $datos); ?>
                            <?php renderCampo('Descripción', 'libro_desc_1', $datos, true); ?>
                            <?php renderCampo('Link Comprar', 'libro_link_comprar', $datos); ?>
                            <?php renderCampo('Link Leer Muestra', 'libro_link_leer', $datos); ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">SECCIÓN SOBRE MÍ</div>
                        <div class="card-body">
                            <?php renderImagen('Foto Perfil', 'sobre_mi_imagen', $datos); ?>
                            <?php renderCampo('Título', 'sobre_mi_titulo', $datos); ?>
                            <?php renderCampo('Texto Biografía', 'sobre_mi_texto', $datos, true); ?>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white">SECCIÓN PODCAST</div>
                        <div class="card-body">
                            <?php renderImagen('Logo Podcast', 'podcast_imagen', $datos); ?>
                            <?php renderCampo('Título Podcast', 'podcast_nombre', $datos); ?>
                            <?php renderCampo('Descripción', 'podcast_desc', $datos, true); ?>
                            <?php renderCampo('Link Spotify', 'podcast_link_spotify', $datos); ?>
                            <?php renderCampo('Link YouTube', 'podcast_link_youtube', $datos); ?>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">FOOTER Y REDES SOCIALES</div>
                        <div class="card-body">
                            <?php renderCampo('Copyright Footer', 'footer_copyright', $datos); ?>
                            <hr>
                            <?php renderCampo('Link Instagram Escritor', 'link_instagram_escritor', $datos); ?>
                            <?php renderCampo('Link Instagram Enigmas', 'link_instagram_enigmas', $datos); ?>
                            <?php renderCampo('Link Instagram Historia', 'link_instagram_historia', $datos); ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="fixed-bottom bg-white p-3 border-top shadow text-end">
                <button type="submit" class="btn btn-success btn-lg px-5 fw-bold"><i class="fas fa-save me-2"></i> GUARDAR TODO</button>
            </div>
        </form>
    </div>
    <br><br><br> </body>
</html>
