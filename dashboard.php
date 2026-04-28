<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: index_admin.php"); exit; }
require '../conexion.php';

// --- FUNCIONES AUXILIARES ---
function guardarBase64($base64, $prefijo) {
    if (empty($base64)) return '';
    if (strpos($base64, 'base64,') !== false) {
        $parts = explode(',', $base64);
        $base64 = end($parts);
    }
    $data = base64_decode($base64);
    if (!$data) return '';
    $nombre = $prefijo . '_' . uniqid() . '.jpg';
    $ruta = "assets/img/uploads/" . $nombre;
    if (!file_exists("../assets/img/uploads/")) mkdir("../assets/img/uploads/", 0777, true);
    file_put_contents("../" . $ruta, $data);
    return $ruta;
}

function guardarArchivo($archivo, $prefijo) {
    $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre = $prefijo . '_' . uniqid() . '.' . $ext;
    $ruta = "assets/img/uploads/" . $nombre;
    if (!file_exists("../assets/img/uploads/")) mkdir("../assets/img/uploads/", 0777, true);
    move_uploaded_file($archivo['tmp_name'], "../" . $ruta);
    return $ruta;
}

// 1. GUARDAR CONFIG GLOBAL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'guardar_config') {
    foreach ($_POST as $k => $v) {
        if ($k != 'accion' && strpos($k, 'img_crop_') === false) {
            $pdo->prepare("INSERT INTO config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?")->execute([$k, $v, $v]);
        }
    }
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'img_crop_') === 0 && !empty($v)) {
            $clave = str_replace('img_crop_', '', $k);
            $ruta = guardarBase64($v, $clave);
            $pdo->prepare("INSERT INTO config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?")->execute([$clave, $ruta, $ruta]);
        }
    }
    $mensaje = "Sitio Web actualizado correctamente";
    $tab = 'global';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'guardar_proyecto') {
    $id = $_POST['id_proyecto'];
    $img = $_POST['img_actual'];
    
    if (!empty($_POST['img_crop_proyecto'])) {
        $img = guardarBase64($_POST['img_crop_proyecto'], 'proy');
    }

    $visible = isset($_POST['visible']) ? 1 : 0;
    $params = [$_POST['titulo'], $_POST['descripcion_corta'], $_POST['descripcion_larga'], $_POST['categoria'], $img, $_POST['url_demo'], $_POST['tecnologias'], $visible];
    
    if ($id) {
        $sql = "UPDATE proyectos SET titulo=?, descripcion_corta=?, descripcion_larga=?, categoria=?, imagen_principal=?, url_demo=?, tecnologias=?, visible=? WHERE id=?";
        $params[] = $id;
    } else {
        $sql = "INSERT INTO proyectos (titulo, descripcion_corta, descripcion_larga, categoria, imagen_principal, url_demo, tecnologias, visible) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    }
    $pdo->prepare($sql)->execute($params);
    $mensaje = "¡Proyecto Guardado!";
    $tab = 'proyectos';
}
// 3. SUBIR A GALERÍA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'subir_galeria') {
    $id_proy = $_POST['id_proyecto_galeria'];
    
    if (isset($_FILES['archivos_galeria'])) {
        $total = count($_FILES['archivos_galeria']['name']);
        for($i=0; $i<$total; $i++) {
            if ($_FILES['archivos_galeria']['error'][$i] == 0) {
                $tmp = $_FILES['archivos_galeria']['tmp_name'][$i];
                $name = $_FILES['archivos_galeria']['name'][$i];
                $type = $_FILES['archivos_galeria']['type'][$i];
                
                $tipo_archivo = (strpos($type, 'video') !== false) ? 'video' : 'imagen';
                $file_array = ['name'=>$name, 'tmp_name'=>$tmp, 'error'=>0];
                $ruta = guardarArchivo($file_array, 'galeria_'.$id_proy);
                
                $pdo->prepare("INSERT INTO proyecto_galeria (proyecto_id, tipo, ruta) VALUES (?, ?, ?)")
                    ->execute([$id_proy, $tipo_archivo, $ruta]);
            }
        }
    }
    $mensaje = "Archivos multimedia agregados";
    $tab = 'proyectos';
}

// 4. BORRAR ITEM GALERÍA
if (isset($_GET['borrar_galeria'])) {
    $pdo->prepare("DELETE FROM proyecto_galeria WHERE id=?")->execute([$_GET['borrar_galeria']]);
    header("Location: dashboard.php?tab=proyectos"); exit;
}

// 5. BORRAR PROYECTO COMPLETO
if (isset($_GET['borrar'])) {
    $pdo->prepare("DELETE FROM proyectos WHERE id=?")->execute([$_GET['borrar']]);
    header("Location: dashboard.php?tab=proyectos"); exit;
}

// --- LEER DATOS ---
$config = $pdo->query("SELECT clave, valor FROM config")->fetchAll(PDO::FETCH_KEY_PAIR);
$proyectos = $pdo->query("SELECT * FROM proyectos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$galeria_raw = $pdo->query("SELECT * FROM proyecto_galeria")->fetchAll(PDO::FETCH_ASSOC);
$galeria = [];
foreach($galeria_raw as $g) {
    $galeria[$g['proyecto_id']][] = $g;
}

$tab_activa = $_GET['tab'] ?? ($tab ?? 'global');

// HELPERS HTML MEJORADOS
function inputTxt($l, $n, $d, $t='text') {
    $v = htmlspecialchars($d[$n] ?? '');
    if($t=='textarea') return "<div class='mb-4'><label class='form-label fw-bold text-muted small text-uppercase'>$l</label><textarea name='$n' class='form-control tinymce' rows='4'>$v</textarea></div>";
    return "<div class='mb-4'><label class='form-label fw-bold text-muted small text-uppercase'>$l</label><input type='text' name='$n' class='form-control form-control-lg bg-light border-0' value='$v'></div>";
}
function inputImg($l, $n, $d, $ratio=1.77, $default = 'https://via.placeholder.com/150') {
    $src = !empty($d[$n]) ? "../".$d[$n]."?t=".time() : $default;
    return "<div class='mb-4 p-4 border-0 bg-light rounded-4 shadow-sm'><label class='form-label fw-bold text-primary small text-uppercase mb-3'><i class='fas fa-image me-2'></i>$l</label><div class='d-flex align-items-center gap-4'><img src='$src' id='preview_$n' style='width:150px;height:auto;object-fit:cover;' class='rounded-3 shadow-sm border'><button type='button' class='btn btn-dark px-4 py-2 fw-bold' onclick=\"iniciarRecorte('$n', $ratio)\"><i class='fas fa-crop-alt me-2'></i>Cambiar Imagen</button><input type='hidden' name='img_crop_$n' id='base64_$n'></div></div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Admin Panel - Federico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f6f9; overflow-x: hidden; }
        
        /* Layout Moderno */
        .sidebar { background: #070707; width: 280px; height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; z-index: 1000; box-shadow: 4px 0 15px rgba(0,0,0,0.1); }
        .main-content { margin-left: 280px; padding: 40px; min-height: 100vh; }
        
        /* Sidebar Links */
        .nav-link-side { color: #888; padding: 16px 24px; font-weight: 600; text-decoration: none; border-left: 4px solid transparent; transition: all 0.3s ease; border-radius: 0 25px 25px 0; margin-right: 15px; margin-bottom: 5px; }
        .nav-link-side:hover { color: #fff; background: rgba(255,255,255,0.05); }
        .nav-link-side.active { color: #00AEEF; border-left-color: #00AEEF; background: rgba(0, 174, 239, 0.1); }
        
        /* Cards Profesionales */
        .card { border-radius: 16px; border: none; box-shadow: 0 8px 30px rgba(0,0,0,0.04); margin-bottom: 30px; overflow: hidden; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 25px; font-size: 1.1rem; font-weight: 700; color: #111; }
        .card-body { padding: 30px; }
        
        /* Botones e Inputs */
        .form-control { border-radius: 8px; padding: 12px 15px; border: 1px solid #e0e0e0; }
        .form-control:focus { box-shadow: 0 0 0 3px rgba(0, 174, 239, 0.2); border-color: #00AEEF; }
        .btn-primary { background: #00AEEF; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 600; }
        .btn-primary:hover { background: #0088b9; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 174, 239, 0.3); }
        
        /* Modales */
        .modal-content { border-radius: 16px; border: none; }
        .modal-header { border-bottom: 1px solid #f0f0f0; padding: 20px 25px; }
        .img-container { height: 60vh; background: #000; overflow: hidden; border-radius: 8px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="p-4 mb-4 text-center border-bottom border-secondary">
        <h3 class="text-white fw-bold mb-0"><i class="fas fa-layer-group" style="color: #00AEEF;"></i> FedePanel</h3>
        <small class="text-muted">Centro de Control</small>
    </div>
    
    <div class="d-flex flex-column gap-2">
        <a href="?tab=global" class="nav-link-side <?= $tab_activa=='global'?'active':'' ?>">
            <i class="fas fa-globe me-3"></i> Textos y Config
        </a>
        <a href="?tab=proyectos" class="nav-link-side <?= $tab_activa=='proyectos'?'active':'' ?>">
            <i class="fas fa-laptop-code me-3"></i> Mis Proyectos
        </a>
    </div>
    
    <div class="mt-auto p-4">
        <a href="../index.php" target="_blank" class="btn btn-outline-light w-100 mb-3 rounded-pill fw-bold">
            <i class="fas fa-external-link-alt me-2"></i> Ver Web Real
        </a>
        <a href="logout.php" class="btn text-white w-100 rounded-pill" style="background: rgba(255,0,0,0.2);">
            <i class="fas fa-power-off me-2 text-danger"></i> Cerrar Sesión
        </a>
    </div>
</div>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold text-dark m-0">
            <?= $tab_activa=='global' ? 'Configuración Global' : 'Gestión de Proyectos' ?>
        </h2>
        <span class="badge bg-white text-dark shadow-sm px-4 py-2 rounded-pill fs-6"><i class="fas fa-user-shield text-success me-2"></i> Modo Admin</span>
    </div>

    <div class="<?= $tab_activa=='global'?'':'d-none' ?>">
        <form method="POST" onsubmit="tinymce.triggerSave();">
            <input type="hidden" name="accion" value="guardar_config">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><i class="fas fa-home text-primary me-2"></i> Sección Hero (Portada)</div>
                        <div class="card-body">
                            <?= inputImg('Imagen de Fondo (16:9)', 'hero_imagen', $config, 1.77) ?>
                            <?= inputTxt('Título Principal', 'hero_titulo', $config) ?>
                            <?= inputTxt('Subtítulo y Bajada', 'hero_subtitulo', $config, 'textarea') ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header"><i class="fas fa-book text-warning me-2"></i> Libros / Escritura</div>
                        <div class="card-body">
                            <?= inputImg('Portada Libro (Vertical)', 'libro_imagen', $config, 0.66) ?>
                            <?= inputTxt('Título del Libro', 'libro_titulo_1', $config) ?>
                            <?= inputTxt('Resumen', 'libro_desc_1', $config, 'textarea') ?>
                            <?= inputTxt('Enlace de Compra', 'libro_link_comprar', $config) ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><i class="fas fa-user-tie text-success me-2"></i> Sobre Mí</div>
                        <div class="card-body">
                            <?= inputImg('Foto Perfil (Cuadrada)', 'sobre_mi_imagen', $config, 1) ?>
                            <?= inputTxt('Título', 'sobre_mi_titulo', $config) ?>
                            <?= inputTxt('Biografía', 'sobre_mi_texto', $config, 'textarea') ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header"><i class="fas fa-podcast text-danger me-2"></i> Podcast</div>
                        <div class="card-body">
                            <?= inputImg('Logo Podcast', 'podcast_imagen', $config, 1) ?>
                            <?= inputTxt('Nombre', 'podcast_nombre', $config) ?>
                            <?= inputTxt('Descripción', 'podcast_desc', $config, 'textarea') ?>
                            <div class="row">
                                <div class="col-6"><?= inputTxt('Link Spotify', 'podcast_link_spotify', $config) ?></div>
                                <div class="col-6"><?= inputTxt('Link YouTube', 'podcast_link_youtube', $config) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header"><i class="fas fa-link text-info me-2"></i> Footer y Redes</div>
                        <div class="card-body">
                            <?= inputTxt('Copyright', 'footer_copyright', $config) ?>
                            <?= inputTxt('Instagram Escritor', 'link_instagram_escritor', $config) ?>
                            <?= inputTxt('Instagram Enigmas', 'link_instagram_enigmas', $config) ?>
                            <?= inputTxt('Instagram Historia', 'link_instagram_historia', $config) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="position-fixed bottom-0 end-0 p-4" style="z-index: 999;">
                <button type="submit" class="btn btn-primary btn-lg shadow-lg rounded-pill px-5">
                    <i class="fas fa-save me-2"></i> GUARDAR TODOS LOS CAMBIOS
                </button>
            </div>
        </form>
    </div>

    <div class="<?= $tab_activa=='proyectos'?'':'d-none' ?>">
        <div class="d-flex justify-content-end mb-4">
            <button class="btn btn-primary shadow-sm" onclick="nuevoProy()">
                <i class="fas fa-plus-circle me-2"></i> CREAR NUEVO PROYECTO
            </button>
        </div>
        
        <div class="row g-4">
            <?php foreach($proyectos as $p): ?>
            <div class="col-lg-6 col-xl-4">
                <div class="card h-100">
                    <div style="height: 200px; overflow: hidden; position: relative;">
                        <img src="../<?= $p['imagen_principal'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <span class="badge bg-dark position-absolute top-0 end-0 m-3 px-3 py-2"><?= htmlspecialchars($p['categoria']) ?></span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="fw-bold mb-2"><?= htmlspecialchars($p['titulo']) ?></h4>
                        <p class="text-muted small mb-3 flex-grow-1"><?= htmlspecialchars($p['descripcion_corta']) ?></p>
                        
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-photo-video me-2 text-warning"></i> <?= count($galeria[$p['id']] ?? []) ?> Archivos extra</span>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex mt-auto">
                            <button class="btn btn-light border fw-bold flex-fill" onclick='editarProy(<?= json_encode($p) ?>)'><i class="fas fa-pen text-primary me-1"></i> Editar</button>
                            <button class="btn btn-light border fw-bold flex-fill" onclick='abrirGaleria(<?= $p["id"] ?>, <?= json_encode($galeria[$p["id"]] ?? []) ?>)'><i class="fas fa-images text-success me-1"></i> Galería</button>
                            <button class="btn btn-light border text-danger flex-fill px-3" onclick="confirmarBorrado('?borrar=<?= $p['id'] ?>', 'Se eliminará todo el proyecto y sus imágenes.')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrop" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-sm-down modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary py-3">
                <h5 class="text-white m-0 fw-bold"><i class="fas fa-crop-alt text-primary me-2"></i>Ajustar Imagen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-black d-flex align-items-center justify-content-center">
                <div class="img-container w-100"><img id="imageToCrop"></div>
            </div>
            <div class="modal-footer border-secondary p-3">
                <button type="button" class="btn btn-primary px-5 fw-bold" id="btnCropConfirm">APLICAR RECORTE</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProy" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" onsubmit="tinymce.triggerSave();">
                <input type="hidden" name="accion" value="guardar_proyecto">
                <input type="hidden" name="id_proyecto" id="p_id">
                <input type="hidden" name="img_actual" id="p_img_actual">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fas fa-laptop-code text-primary me-2"></i>Información del Proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="bg-white p-4 rounded shadow-sm text-center border h-100">
                                <label class="d-block small fw-bold text-muted text-uppercase mb-3">Portada Principal</label>
                                <img id="p_preview_img" src="" style="width:100%; aspect-ratio:4/3; object-fit:cover;" class="rounded shadow-sm mb-3">
                                <button type="button" class="btn btn-outline-dark w-100 fw-bold" onclick="iniciarRecorte('proyecto', 1.33)"><i class="fas fa-camera me-2"></i> Subir Portada</button>
                                <input type="hidden" name="img_crop_proyecto" id="base64_proyecto">
                            </div>
                        </div>
                        
                        <div class="col-lg-8">
                            <div class="bg-white p-4 rounded shadow-sm border h-100">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold small text-muted">Título del Proyecto</label>
                                        <input type="text" name="titulo" id="p_tit" class="form-control form-control-lg bg-light" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted">Categoría</label>
                                        <select name="categoria" id="p_cat" class="form-select form-select-lg bg-light">
                                            <option value="logistica">Logística</option>
                                            <option value="educacion">Educación</option>
                                            <option value="salud">Salud</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-muted">Descripción Corta (Tarjeta)</label>
                                        <input type="text" name="descripcion_corta" id="p_dc" class="form-control bg-light" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-muted">Descripción Larga Completa</label>
                                        <textarea name="descripcion_larga" id="p_dl" class="form-control tinymce" style="height:200px"></textarea>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="visible" id="p_vis" checked>
                                        <label class="form-check-label small fw-bold text-muted">Proyecto Activo (Visible en la web)</label>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Tecnologías (separadas por coma)</label>
                                        <input type="text" name="tecnologias" id="p_tec" class="form-control bg-light">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">URL Demo (Opcional)</label>
                                        <input type="text" name="url_demo" id="p_url" class="form-control bg-light">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-white">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold">GUARDAR PROYECTO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGaleria" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold"><i class="fas fa-photo-video text-success me-2"></i>Galería Multimedia Extra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm border mb-4">
                    <input type="hidden" name="accion" value="subir_galeria">
                    <input type="hidden" name="id_proyecto_galeria" id="g_id">
                    <label class="form-label fw-bold text-dark">Añadir Archivos (Imágenes o Videos MP4)</label>
                    <div class="input-group input-group-lg">
                        <input type="file" name="archivos_galeria[]" class="form-control" multiple accept="image/*,video/mp4" required>
                        <button class="btn btn-success px-4 fw-bold"><i class="fas fa-upload me-2"></i> SUBIR</button>
                    </div>
                    <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i> Selecciona múltiples archivos presionando CTRL o SHIFT en tu computadora.</small>
                </form>

                <h6 class="fw-bold mb-3 text-muted text-uppercase">Contenido de la Galería:</h6>
                <div class="row g-3" id="galeria_container">
                </div>
            </div>
        </div>
    </div>
</div>

<input type="file" id="fileInputGlobal" accept="image/*" style="display:none">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<script>
// --- INICIALIZAR TINYMCE (EDITOR DE TEXTO) ---
tinymce.init({
    selector: 'textarea.tinymce',
    menubar: false,
    plugins: 'lists link code textcolor formatpainter',
    toolbar: 'undo redo | formatselect | bold italic textcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link code',
    skin: 'oxide',
    height: 300,
    setup: function (editor) {
        editor.on('change', function () {
            tinymce.triggerSave();
        });
    }
});

// Arreglo para que TinyMCE funcione bien dentro de un Modal de Bootstrap
document.addEventListener('focusin', (e) => {
    if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
        e.stopImmediatePropagation();
    }
});

// --- ALERTAS SWEETALERT2 PARA PHP ---
<?php if(isset($mensaje)): ?>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '<?= $mensaje ?>',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
    });
});
<?php endif; ?>

// Alerta de confirmación de borrado
function confirmarBorrado(url, mensaje) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: mensaje,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash me-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}


// --- LÓGICA DEL RECORTE DE IMÁGENES (CROPPER) ---
let cropper, fieldId, ratio;
const fileIn = document.getElementById('fileInputGlobal');
const imgEl = document.getElementById('imageToCrop');
const cropModal = new bootstrap.Modal(document.getElementById('modalCrop'));
const proyModal = new bootstrap.Modal(document.getElementById('modalProy'));
const galModal = new bootstrap.Modal(document.getElementById('modalGaleria'));

function iniciarRecorte(id, r) {
    fieldId = 'base64_' + id; ratio = r; fileIn.value = ''; fileIn.click();
}

fileIn.addEventListener('change', e => {
    if(e.target.files[0]) {
        let r = new FileReader();
        r.onload = evt => { imgEl.src = evt.target.result; cropModal.show(); };
        r.readAsDataURL(e.target.files[0]);
    }
});

document.getElementById('modalCrop').addEventListener('shown.bs.modal', () => {
    if(cropper) cropper.destroy();
    cropper = new Cropper(imgEl, { aspectRatio: ratio, viewMode: 1, dragMode:'move', autoCropArea:1, background: false });
});

document.getElementById('btnCropConfirm').addEventListener('click', () => {
    if(cropper) {
        let cvs = cropper.getCroppedCanvas({width:800});
        let b64 = cvs.toDataURL('image/jpeg', 0.85);
        document.getElementById(fieldId).value = b64;
        let prevId = (fieldId == 'base64_proyecto') ? 'p_preview_img' : fieldId.replace('base64_','preview_');
        if(document.getElementById(prevId)) document.getElementById(prevId).src = b64;
        cropModal.hide();
    }
});

// --- LÓGICA DE PROYECTOS ---
function nuevoProy() {
    document.getElementById('p_id').value=''; document.getElementById('p_img_actual').value='';
    document.getElementById('base64_proyecto').value=''; document.getElementById('p_preview_img').src='https://via.placeholder.com/100';
    ['p_tit','p_cat','p_dc','p_dl','p_tec','p_url'].forEach(i=>document.getElementById(i).value='');
    document.getElementById('p_vis').checked = true;
    proyModal.show();
}

function editarProy(p) {
    document.getElementById('p_id').value=p.id; document.getElementById('p_img_actual').value=p.imagen_principal;
    document.getElementById('base64_proyecto').value='';
    document.getElementById('p_preview_img').src = p.imagen_principal ? '../'+p.imagen_principal : '';
    document.getElementById('p_tit').value=p.titulo; document.getElementById('p_cat').value=p.categoria;
    document.getElementById('p_dc').value=p.descripcion_corta; document.getElementById('p_dl').value=p.descripcion_larga;
    document.getElementById('p_tec').value=p.tecnologias; document.getElementById('p_url').value=p.url_demo;
    document.getElementById('p_vis').checked = (p.visible == 1);
    proyModal.show();
}

// --- LÓGICA DE GALERÍA ---
function abrirGaleria(id, archivos) {
    document.getElementById('g_id').value = id;
    const cont = document.getElementById('galeria_container');
    cont.innerHTML = '';
    
    if(archivos.length === 0) {
        cont.innerHTML = '<div class="col-12"><div class="alert alert-light border text-center text-muted py-4"><i class="fas fa-ghost fa-2x mb-2 d-block"></i> No hay archivos. Se mostrará solo la portada.</div></div>';
    } else {
        archivos.forEach(a => {
            let preview = '';
            if(a.tipo === 'video') {
                preview = `<div class="ratio ratio-16x9 bg-black rounded-top"><video src="../${a.ruta}"></video></div>`;
            } else {
                preview = `<img src="../${a.ruta}" class="w-100 rounded-top" style="height:120px; object-fit:cover;">`;
            }
            
            cont.innerHTML += `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm border-0 bg-white">
                        ${preview}
                        <div class="card-footer bg-white border-0 p-2 text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold" onclick="confirmarBorrado('?borrar_galeria=${a.id}', '¿Seguro que quieres borrar este archivo de la galería?')"><i class="fas fa-trash me-1"></i> Borrar</button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    galModal.show();
}
</script>
</body>
</html>