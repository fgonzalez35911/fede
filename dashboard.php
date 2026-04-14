<?php
session_start();
// SEGURIDAD: Si no es admin, mandar al nuevo login index_admin.php
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
    $mensaje = "¡Web Actualizada!";
    $tab = 'global';
}

// 2. GUARDAR PROYECTO (Info principal + Foto portada)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'guardar_proyecto') {
    $id = $_POST['id_proyecto'];
    $img = $_POST['img_actual'];
    
    if (!empty($_POST['img_crop_proyecto'])) {
        $img = guardarBase64($_POST['img_crop_proyecto'], 'proy');
    }

    $params = [$_POST['titulo'], $_POST['descripcion_corta'], $_POST['descripcion_larga'], $_POST['categoria'], $img, $_POST['url_demo'], $_POST['tecnologias']];
    
    if ($id) {
        $sql = "UPDATE proyectos SET titulo=?, descripcion_corta=?, descripcion_larga=?, categoria=?, imagen_principal=?, url_demo=?, tecnologias=? WHERE id=?";
        $params[] = $id;
    } else {
        $sql = "INSERT INTO proyectos (titulo, descripcion_corta, descripcion_larga, categoria, imagen_principal, url_demo, tecnologias) VALUES (?, ?, ?, ?, ?, ?, ?)";
    }
    $pdo->prepare($sql)->execute($params);
    $mensaje = "¡Proyecto Guardado!";
    $tab = 'proyectos';
}

// 3. SUBIR A GALERÍA (NUEVO: Soporte Fotos y Videos)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'subir_galeria') {
    $id_proy = $_POST['id_proyecto_galeria'];
    
    if (isset($_FILES['archivos_galeria'])) {
        $total = count($_FILES['archivos_galeria']['name']);
        for($i=0; $i<$total; $i++) {
            if ($_FILES['archivos_galeria']['error'][$i] == 0) {
                $tmp = $_FILES['archivos_galeria']['tmp_name'][$i];
                $name = $_FILES['archivos_galeria']['name'][$i];
                $type = $_FILES['archivos_galeria']['type'][$i];
                
                // Detectar si es video o imagen
                $tipo_archivo = (strpos($type, 'video') !== false) ? 'video' : 'imagen';
                
                // Simular array de archivo único para la función
                $file_array = ['name'=>$name, 'tmp_name'=>$tmp, 'error'=>0];
                $ruta = guardarArchivo($file_array, 'galeria_'.$id_proy);
                
                $pdo->prepare("INSERT INTO proyecto_galeria (proyecto_id, tipo, ruta) VALUES (?, ?, ?)")
                    ->execute([$id_proy, $tipo_archivo, $ruta]);
            }
        }
    }
    $mensaje = "¡Archivos agregados a la galería!";
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

// Leer Galería Completa
$galeria_raw = $pdo->query("SELECT * FROM proyecto_galeria")->fetchAll(PDO::FETCH_ASSOC);
$galeria = [];
foreach($galeria_raw as $g) {
    $galeria[$g['proyecto_id']][] = $g;
}

$tab_activa = $_GET['tab'] ?? ($tab ?? 'global');

// HELPERS HTML
function inputTxt($l, $n, $d, $t='text') {
    $v = htmlspecialchars($d[$n] ?? '');
    if($t=='textarea') return "<div class='mb-2'><label class='fw-bold small'>$l</label><textarea name='$n' class='form-control'>$v</textarea></div>";
    return "<div class='mb-2'><label class='fw-bold small'>$l</label><input type='text' name='$n' class='form-control' value='$v'></div>";
}
function inputImg($l, $n, $d, $ratio=1.77) {
    $src = !empty($d[$n]) ? "../".$d[$n]."?t=".time() : "https://via.placeholder.com/100";
    return "<div class='mb-3 p-2 border bg-white rounded'><label class='small fw-bold text-primary'>$l</label><div class='d-flex align-items-center gap-2 mt-1'><img src='$src' id='preview_$n' style='width:60px;height:40px;object-fit:cover;' class='rounded bg-light'><button type='button' class='btn btn-sm btn-dark' onclick=\"iniciarRecorte('$n', $ratio)\">Cambiar</button><input type='hidden' name='img_crop_$n' id='base64_$n'></div></div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Admin Fede</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
        #modalCrop { z-index: 1060 !important; }
        .img-container { height: 50vh; background: #000; overflow: hidden; }
        img { max-width: 100%; }
        .nav-pills .nav-link.active { background-color: #0d6efd; }
    </style>
</head>
<body class="bg-light pb-5">

<nav class="navbar navbar-dark bg-dark sticky-top p-2">
    <div class="d-flex w-100 justify-content-between align-items-center">
        <span class="text-white fw-bold">Admin Panel</span>
        <div>
            <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-light me-1">Ver Web</a>
            <a href="logout.php" class="btn btn-sm btn-danger">Salir</a>
        </div>
    </div>
</nav>

<div class="container mt-3">
    <?php if(isset($mensaje)): ?><div class="alert alert-success py-1"><?= $mensaje ?></div><?php endif; ?>

    <ul class="nav nav-pills nav-fill mb-3 bg-white p-1 rounded shadow-sm">
        <li class="nav-item"><a class="nav-link <?= $tab_activa=='global'?'active':'' ?>" href="?tab=global">Global</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab_activa=='proyectos'?'active':'' ?>" href="?tab=proyectos">Proyectos</a></li>
    </ul>

    <div class="tab-content">
        <div class="<?= $tab_activa=='global'?'':'d-none' ?>">
            <form method="POST">
                <input type="hidden" name="accion" value="guardar_config">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header fw-bold bg-secondary text-white">Hero (Portada)</div>
                    <div class="card-body">
                        <?= inputImg('Fondo (16:9)', 'hero_imagen', $config, 1.77) ?>
                        <?= inputTxt('Título', 'hero_titulo', $config) ?>
                        <?= inputTxt('Subtítulo', 'hero_subtitulo', $config, 'textarea') ?>
                    </div>
                </div>
                <div class="card mb-3 shadow-sm">
                    <div class="card-header fw-bold bg-info text-white">Sobre Mí</div>
                    <div class="card-body">
                        <?= inputImg('Foto Perfil (Cuadrada)', 'sobre_mi_imagen', $config, 1) ?>
                        <?= inputTxt('Título', 'sobre_mi_titulo', $config) ?>
                        <?= inputTxt('Biografía', 'sobre_mi_texto', $config, 'textarea') ?>
                    </div>
                </div>
                <div class="card mb-3 shadow-sm">
                    <div class="card-header fw-bold bg-warning text-dark">Libros</div>
                    <div class="card-body">
                        <?= inputImg('Portada (Vertical)', 'libro_imagen', $config, 0.66) ?>
                        <?= inputTxt('Título', 'libro_titulo_1', $config) ?>
                        <?= inputTxt('Descripción', 'libro_desc_1', $config, 'textarea') ?>
                        <?= inputTxt('Link Comprar', 'libro_link_comprar', $config) ?>
                    </div>
                </div>
                <div class="d-grid pb-5"><button class="btn btn-success btn-lg shadow">GUARDAR TODO</button></div>
            </form>
        </div>

        <div class="<?= $tab_activa=='proyectos'?'':'d-none' ?>">
            <button class="btn btn-primary w-100 mb-3" onclick="nuevoProy()">+ NUEVO PROYECTO</button>
            <div class="row g-3">
                <?php foreach($proyectos as $p): ?>
                <div class="col-12 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="d-flex p-2 gap-2 align-items-center">
                            <img src="../<?= $p['imagen_principal'] ?>" style="width:70px;height:50px;object-fit:cover;" class="rounded bg-light">
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="mb-0 text-truncate"><?= htmlspecialchars($p['titulo']) ?></h6>
                                <small class="text-muted"><?= count($galeria[$p['id']] ?? []) ?> archivos extra</small>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-fill" onclick='editarProy(<?= json_encode($p) ?>)'>Editar Info</button>
                            <button class="btn btn-sm btn-outline-warning flex-fill" onclick='abrirGaleria(<?= $p["id"] ?>, <?= json_encode($galeria[$p["id"]] ?? []) ?>)'>Galería</button>
                            <a href="?borrar=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de borrar este proyecto?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrop" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-sm-down modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0 py-2"><h6 class="text-white m-0">Ajustar Imagen</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0 bg-black d-flex align-items-center justify-content-center"><div class="img-container w-100"><img id="imageToCrop"></div></div>
            <div class="modal-footer border-0 bg-black p-2"><button type="button" class="btn btn-success w-100 fw-bold" id="btnCropConfirm">CONFIRMAR</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProy" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="accion" value="guardar_proyecto">
                <input type="hidden" name="id_proyecto" id="p_id">
                <input type="hidden" name="img_actual" id="p_img_actual">
                <div class="modal-header bg-light"><h5 class="modal-title">Info Proyecto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="text-center mb-3 bg-light p-2 border rounded">
                        <label class="d-block small fw-bold mb-2">Portada Principal (Grid)</label>
                        <img id="p_preview_img" src="" style="width:120px;height:90px;object-fit:cover;" class="rounded border mb-2">
                        <br><button type="button" class="btn btn-sm btn-dark" onclick="iniciarRecorte('proyecto', 1.33)">Subir Portada</button>
                        <input type="hidden" name="img_crop_proyecto" id="base64_proyecto">
                    </div>
                    <div class="form-floating mb-2"><input type="text" name="titulo" id="p_tit" class="form-control" placeholder="T" required><label>Título</label></div>
                    <div class="form-floating mb-2"><select name="categoria" id="p_cat" class="form-select"><option value="logistica">Logística</option><option value="educacion">Educación</option><option value="salud">Salud</option></select><label>Categoría</label></div>
                    <div class="form-floating mb-2"><input type="text" name="descripcion_corta" id="p_dc" class="form-control" placeholder="C" required><label>Desc. Corta</label></div>
                    <div class="form-floating mb-2"><textarea name="descripcion_larga" id="p_dl" class="form-control" style="height:100px" placeholder="L"></textarea><label>Desc. Larga</label></div>
                    <div class="row g-2">
                        <div class="col-6"><div class="form-floating"><input type="text" name="tecnologias" id="p_tec" class="form-control" placeholder="Tec"><label>Tecnologías</label></div></div>
                        <div class="col-6"><div class="form-floating"><input type="text" name="url_demo" id="p_url" class="form-control" placeholder="Url"><label>URL Demo</label></div></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary w-100">GUARDAR INFO</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGaleria" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning"><h5 class="modal-title text-dark">Galería y Videos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" class="mb-4 border-bottom pb-3">
                    <input type="hidden" name="accion" value="subir_galeria">
                    <input type="hidden" name="id_proyecto_galeria" id="g_id">
                    <label class="form-label fw-bold">Subir Archivos (Fotos o Videos MP4)</label>
                    <div class="input-group">
                        <input type="file" name="archivos_galeria[]" class="form-control" multiple accept="image/*,video/mp4">
                        <button class="btn btn-success">SUBIR</button>
                    </div>
                    <small class="text-muted">Podés seleccionar múltiples archivos a la vez.</small>
                </form>

                <h6 class="fw-bold">Contenido Actual:</h6>
                <div class="row g-2" id="galeria_container">
                    </div>
            </div>
        </div>
    </div>
</div>

<input type="file" id="fileInputGlobal" accept="image/*" style="display:none">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
let cropper, fieldId, ratio;
const fileIn = document.getElementById('fileInputGlobal');
const imgEl = document.getElementById('imageToCrop');
const cropModal = new bootstrap.Modal(document.getElementById('modalCrop'));
const proyModal = new bootstrap.Modal(document.getElementById('modalProy'));
const galModal = new bootstrap.Modal(document.getElementById('modalGaleria'));

// Funciones de Recorte
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
    cropper = new Cropper(imgEl, { aspectRatio: ratio, viewMode: 1, dragMode:'move', autoCropArea:1 });
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

// Funciones Proyectos
function nuevoProy() {
    document.getElementById('p_id').value=''; document.getElementById('p_img_actual').value='';
    document.getElementById('base64_proyecto').value=''; document.getElementById('p_preview_img').src='https://via.placeholder.com/100';
    ['p_tit','p_cat','p_dc','p_dl','p_tec','p_url'].forEach(i=>document.getElementById(i).value='');
    proyModal.show();
}

function editarProy(p) {
    document.getElementById('p_id').value=p.id; document.getElementById('p_img_actual').value=p.imagen_principal;
    document.getElementById('base64_proyecto').value='';
    document.getElementById('p_preview_img').src = p.imagen_principal ? '../'+p.imagen_principal : '';
    document.getElementById('p_tit').value=p.titulo; document.getElementById('p_cat').value=p.categoria;
    document.getElementById('p_dc').value=p.descripcion_corta; document.getElementById('p_dl').value=p.descripcion_larga;
    document.getElementById('p_tec').value=p.tecnologias; document.getElementById('p_url').value=p.url_demo;
    proyModal.show();
}

// Función Galería (Renderiza el modal con los archivos)
function abrirGaleria(id, archivos) {
    document.getElementById('g_id').value = id;
    const cont = document.getElementById('galeria_container');
    cont.innerHTML = '';
    
    if(archivos.length === 0) {
        cont.innerHTML = '<p class="text-muted small ms-2">No hay archivos extra. Se mostrará solo la portada en el carrusel.</p>';
    } else {
        archivos.forEach(a => {
            let preview = '';
            if(a.tipo === 'video') {
                preview = `<div class="ratio ratio-16x9 mb-1 bg-black rounded"><video src="../${a.ruta}"></video></div>`;
            } else {
                preview = `<img src="../${a.ruta}" class="img-fluid rounded mb-1" style="height:100px;width:100%;object-fit:cover;">`;
            }
            
            cont.innerHTML += `
                <div class="col-4 col-md-3">
                    <div class="border p-2 bg-white rounded position-relative shadow-sm h-100">
                        ${preview}
                        <div class="text-center">
                            <a href="?borrar_galeria=${a.id}" class="btn btn-danger btn-sm w-100 py-0" style="font-size:0.7rem" onclick="return confirm('¿Borrar archivo?')">Borrar</a>
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