<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit; }
require '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lista de campos permitidos
    $campos = ['sobre_mi_titulo', 'sobre_mi_texto', 'footer_copy', 'link_linkedin', 'link_github', 'link_email'];
    
    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            $stmt = $pdo->prepare("INSERT INTO config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
            $stmt->execute([$campo, $_POST[$campo], $_POST[$campo]]);
        }
    }
    $mensaje = "Textos actualizados.";
}

$conf = $pdo->query("SELECT * FROM config")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Textos - FedeAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'menu.php'; ?>
    
    <div class="container pb-5">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-info text-dark fw-bold">Sección: Sobre Mí</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Título</label>
                                <input type="text" name="sobre_mi_titulo" class="form-control" value="<?= $conf['sobre_mi_titulo'] ?? 'Sobre Mí' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Texto Descripción</label>
                                <textarea name="sobre_mi_texto" class="form-control" rows="6"><?= $conf['sobre_mi_texto'] ?? '' ?></textarea>
                            </div>
                            <button class="btn btn-primary btn-sm">Guardar Texto</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-dark text-white fw-bold">Footer y Redes</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Copyright Footer</label>
                                <input type="text" name="footer_copy" class="form-control" value="<?= $conf['footer_copy'] ?? '© 2025 Fede.' ?>">
                            </div>
                            <hr>
                            <h6>Enlaces (Pon la URL completa)</h6>
                            <div class="mb-2 input-group">
                                <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                                <input type="text" name="link_linkedin" class="form-control" value="<?= $conf['link_linkedin'] ?? '#' ?>">
                            </div>
                            <div class="mb-2 input-group">
                                <span class="input-group-text"><i class="fab fa-github"></i></span>
                                <input type="text" name="link_github" class="form-control" value="<?= $conf['link_github'] ?? '#' ?>">
                            </div>
                            <div class="mb-2 input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="text" name="link_email" class="form-control" value="<?= $conf['link_email'] ?? '#' ?>">
                            </div>
                            <button class="btn btn-primary btn-sm w-100 mt-2">Guardar Footer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
