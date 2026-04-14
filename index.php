<?php
require 'conexion.php';

// --- LÓGICA DE BASE DE DATOS ---
$config = [];
$proyectos = [];
$galeria = []; // Array para guardar las fotos extra

try {
    // 1. Cargar Configuración
    $stmt_c = $pdo->query("SELECT clave, valor FROM config");
    if($stmt_c) $config = $stmt_c->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // 2. Cargar Proyectos
    $stmt_p = $pdo->query("SELECT * FROM proyectos ORDER BY orden ASC, id DESC");
    if($stmt_p) $proyectos = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

    // 3. Cargar Galería (Fotos y Videos extra)
    $stmt_g = $pdo->query("SELECT * FROM proyecto_galeria ORDER BY id ASC");
    $raw_gal = $stmt_g->fetchAll(PDO::FETCH_ASSOC);
    // Organizar las fotos por ID de proyecto
    foreach($raw_gal as $g) {
        $galeria[$g['proyecto_id']][] = $g;
    }

} catch(Exception $e) {
    // Si falla la DB, no mostramos error fatal
}

function d($k, $def, $c) { return !empty($c[$k]) ? $c[$k] : $def; }

$pageTitle = 'Federico González'; $metaDescription = 'Portfolio';
include 'includes/header.php';
?>

<section id="hero" class="hero vh-100 d-flex align-items-center">
    <div class="container z-1 text-center text-white">
        <h1 class="display-2 fw-bold mb-2">
            <?php if(!empty($config['hero_titulo'])): ?><?= $config['hero_titulo'] ?><?php else: ?><span id="typed-hero"></span><?php endif; ?>
        </h1>
        <p class="fs-4 mb-5"><?= d('hero_subtitulo', 'Desarrollo Software...', $config) ?></p>
        <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
            <a href="#portfolio" class="btn btn-lg btn-accent-blue-outline"><i class="fas fa-code me-2"></i> Ver Portfolio</a>
            <a href="#writing" class="btn btn-lg btn-accent-orange"><i class="fas fa-book me-2"></i> Obra Publicada</a>
        </div>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-bg-img" style="background-image: url('<?= d('hero_imagen', 'assets/img/hero.webp', $config) ?>') !important;"></div> 
    <a href="#portfolio" class="scroll-down-link"><div class="scroll-down-mouse"><div class="scroll-down-wheel"></div></div></a>
</section>

<section id="portfolio" class="py-5 bg-darker">
    <div class="container">
        <h2 class="display-4 text-white text-center mb-5" data-aos="fade-up"><i class="fas fa-laptop-code text-accent-blue me-3"></i>Proyectos</h2>
        <p class="lead text-center text-white mb-5" data-aos="fade-up"><?= d('portfolio_subtitulo', 'Soluciones a medida.', $config) ?></p>
        
        <div class="text-center mb-5" id="portfolio-filters">
            <button class="btn btn-filter btn-accent-blue-outline active" data-filter="*">Todos</button>
            <button class="btn btn-filter btn-accent-blue-outline" data-filter="logistica">Logística</button>
            <button class="btn btn-filter btn-accent-blue-outline" data-filter="educacion">Educación</button>
            <button class="btn btn-filter btn-accent-blue-outline" data-filter="salud">Salud</button>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4" id="portfolio-grid">
            <?php foreach($proyectos as $p): ?>
            <div class="col portfolio-item" data-category="<?= htmlspecialchars($p['categoria']) ?>">
                <div class="card bg-black text-white h-100 shadow-lg project-card">
                    
                    <div style="width:100%; aspect-ratio:4/3; overflow:hidden; position:relative; cursor: pointer;" 
                         data-bs-toggle="modal" 
                         data-bs-target="#projectModal<?= $p['id'] ?>"
                         title="Ver detalles de <?= htmlspecialchars($p['titulo']) ?>">
                        <img src="<?= htmlspecialchars($p['imagen_principal']) ?>" style="width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0; transition: transform 0.3s ease;" class="hover-zoom" alt="Img">
                    </div>

                    <div class="card-body">
                        <h5 class="text-accent-blue" 
                            style="cursor: pointer;" 
                            data-bs-toggle="modal" 
                            data-bs-target="#projectModal<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['titulo']) ?>
                        </h5>
                        
                        <p class="text-white small"><?= htmlspecialchars($p['descripcion_corta']) ?></p>
                        <?php if(!empty($p['tecnologias'])): foreach(explode(',', $p['tecnologias']) as $t): ?>
                            <span class="badge bg-secondary me-1"><?= trim($t) ?></span>
                        <?php endforeach; endif; ?>
                    </div>
                    
                    <div class="card-footer border-0 bg-transparent">
                         <button class="btn btn-sm btn-accent-blue" data-bs-toggle="modal" data-bs-target="#projectModal<?= $p['id'] ?>">Ver Detalles <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="writing" class="py-5">
    <div class="container">
        <h2 class="display-4 text-white text-center mb-5" data-aos="fade-up"><i class="fas fa-feather-alt text-accent-orange me-3"></i><?= d('libro_titulo_seccion', 'Mi Obra', $config) ?></h2>
        <div class="row align-items-center g-5">
            <div class="col-md-5 text-center" data-aos="fade-right">
                <img src="<?= d('libro_imagen', 'assets/img/libro1.png', $config) ?>" class="img-fluid rounded shadow-lg book-cover-img">
            </div>
            <div class="col-md-7 text-white text-md-start text-center" data-aos="fade-left">
                <h3 class="display-5 text-accent-orange mb-3"><?= d('libro_titulo_1', '', $config) ?></h3>
                <p class="lead mb-4"><?= d('libro_desc_1', '', $config) ?></p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <a target="_blank" href="<?= d('libro_link_comprar', '#', $config) ?>" class="btn btn-lg btn-accent-orange"><i class="fas fa-shopping-cart me-2"></i> Comprar</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="history" class="py-5 bg-darker text-white">
    <div class="container">
        <h2 class="display-4 text-center mb-5" data-aos="fade-up"><i class="fas fa-history text-accent-orange me-3"></i><?= d('historia_titulo', '', $config) ?></h2>
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-lg-2 text-center" data-aos="fade-left">
                <video class="img-fluid rounded shadow-lg history-video" loop muted autoplay playsinline poster="<?= d('historia_imagen', 'assets/img/video-poster.jpg', $config) ?>">
                    <source src="assets/video/Historia_Humana_y_Futuro.mp4" type="video/mp4">
                </video>
            </div>
            <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                <p class="lead text-white-75"><?= d('historia_texto_1', '', $config) ?></p>
            </div>
        </div>
    </div>
</section>

<section id="nuevo-amanecer" class="py-5 bg-darker text-white">
    <div class="container">
        <h2 class="display-4 text-white text-center mb-5" data-aos="fade-up"><i class="fas fa-satellite-dish text-accent-blue me-3"></i><?= d('amanecer_titulo', '', $config) ?></h2>
        <div class="row align-items-center g-5">
            <div class="col-md-5 text-center" data-aos="fade-right">
                <div class="book-cover-wrapper">
                    <img src="<?= d('amanecer_imagen', 'assets/img/portada-nuevo-amanecer.png', $config) ?>" width="250px" class="img-fluid rounded shadow-lg book-cover-img">
                </div>
            </div>
            <div class="col-md-7 text-white text-md-start text-center" data-aos="fade-left">
                <h3 class="display-5 text-accent-blue mb-3"><?= d('amanecer_subtitulo', '', $config) ?></h3>
                <p class="lead mb-4"><?= d('amanecer_desc', '', $config) ?></p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <button type="button" class="btn btn-lg btn-accent-blue" onclick="abrirModalTrailer('<?= d('amanecer_link_video', '#', $config) ?>')">
                        <i class="fas fa-play-circle me-2"></i> Ver Tráiler
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="podcast" class="py-5 bg-black text-center text-white">
    <div class="container">
        <h2 class="display-4 text-white mb-5" data-aos="fade-up"><i class="fas fa-headphones-alt text-accent-blue me-3"></i><?= d('podcast_titulo_seccion', '', $config) ?></h2>
        <div class="row align-items-center justify-content-center g-5 mb-5">
            <div class="col-md-5" data-aos="fade-up"><img src="<?= d('podcast_imagen', 'assets/img/enigmas-sin-fronteras.png', $config) ?>" class="img-fluid rounded-circle shadow-lg podcast-logo-img"></div>
            <div class="col-md-7 text-md-start" data-aos="fade-up">
                <h3 class="display-5 text-accent-blue mb-3"><?= d('podcast_nombre', '', $config) ?></h3>
                <p class="lead mb-4"><?= d('podcast_desc', '', $config) ?></p>
                <div class="d-grid gap-3 d-sm-flex justify-content-sm-center justify-content-md-start">
                    <a href="<?= d('podcast_link_spotify', '#', $config) ?>" target="_blank" class="btn btn-lg btn-podcast-spotify"><i class="fab fa-spotify me-2"></i> Spotify</a>
                    <a href="<?= d('podcast_link_youtube', '#', $config) ?>" target="_blank" class="btn btn-lg btn-podcast-youtube"><i class="fab fa-youtube me-2"></i> YouTube</a>
                </div>
            </div>
        </div>
        
        <?php
/*
<h3 class="display-6 mt-5 mb-4 text-white-50" data-aos="fade-up">Últimos Episodios:</h3>
        <div class="row row-cols-1 row-cols-md-3 g-4" data-aos="fade-up"><?php include 'includes/podcast_episodes.php'; ?></div>
*/
?>                        


    </div>
</section>

<section id="about" class="py-5 bg-darker">
    <div class="container">
        <h2 class="display-4 text-white text-center mb-5" data-aos="fade-up"><i class="fas fa-user-tie me-3"></i><?= d('sobre_mi_titulo', '', $config) ?></h2>
        <div class="row align-items-center g-5">
            <div class="col-md-4 text-center" data-aos="fade-right">
                <img src="<?= d('sobre_mi_imagen', 'assets/img/perfil.jpg', $config) ?>" class="img-fluid rounded-circle shadow-lg profile-img">
                <h4 class="mt-3 text-accent-blue">Federico González</h4>
            </div>
            <div class="col-md-8 text-white" data-aos="fade-left"><div class="lead"><?= nl2br(d('sobre_mi_texto', '', $config)) ?></div></div>
        </div>
    </div>
</section>

<section id="proceso" class="py-5 bg-black text-white text-center">
    <div class="container">
        <h2 class="display-4 mb-5"><i class="fas fa-project-diagram text-accent-blue me-3"></i>Mi Proceso</h2>
        <div class="row g-4">
            <div class="col-md-3"><div class="proceso-step p-4"><h5>1. Análisis</h5></div></div>
            <div class="col-md-3"><div class="proceso-step p-4"><h5>2. Diseño</h5></div></div>
            <div class="col-md-3"><div class="proceso-step p-4"><h5>3. Desarrollo</h5></div></div>
            <div class="col-md-3"><div class="proceso-step p-4"><h5>4. Despliegue</h5></div></div>
        </div>
    </div>
</section>

<section id="instagram" class="py-5 bg-darker text-white text-center">
    <div class="container">
        <h2 class="display-4 mb-5" data-aos="fade-up"><i class="fab fa-instagram text-accent-orange me-3"></i>Instagram</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col"><div class="card bg-darker text-white h-100 shadow-lg instagram-card"><img src="assets/img/escritor-ig.png" class="card-img-top"><div class="card-body"><h5 class="text-accent-orange">@fedegonzalez.escritor</h5><a href="<?= d('link_instagram_escritor', '#', $config) ?>" target="_blank" class="btn btn-sm btn-accent-orange-outline mt-2">Seguir</a></div></div></div>
            <div class="col"><div class="card bg-darker text-white h-100 shadow-lg instagram-card"><img src="assets/img/enigmas2-ig.png" class="card-img-top"><div class="card-body"><h5 class="text-accent-blue">@enigmassinfronteras</h5><a href="<?= d('link_instagram_enigmas', '#', $config) ?>" target="_blank" class="btn btn-sm btn-accent-blue-outline mt-2">Seguir</a></div></div></div>
            <div class="col"><div class="card bg-darker text-white h-100 shadow-lg instagram-card"><img src="assets/img/historia-ig.png" class="card-img-top"><div class="card-body"><h5 class="text-accent-orange">@lahistoriadenuestrahumanidad</h5><a href="<?= d('link_instagram_historia', '#', $config) ?>" target="_blank" class="btn btn-sm btn-accent-orange-outline mt-2">Seguir</a></div></div></div>
        </div>
    </div>
</section>

<?php foreach($proyectos as $p): ?>
<?php $imgs = $galeria[$p['id']] ?? []; ?>

<div class="modal fade" id="projectModal<?= $p['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $p['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content bg-darker text-white border-0 shadow-lg">
      
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title text-accent-blue fw-bold" id="modalLabel<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body p-4">
        <div class="row g-4">
            <div class="col-lg-7">
                <div id="carouselProject<?= $p['id'] ?>" class="carousel slide border border-secondary rounded shadow-sm bg-black position-relative" data-bs-ride="carousel">
                  
                  <div class="carousel-inner" style="min-height: 400px; display:flex; align-items:center;">
                    <div class="carousel-item active" data-index="0">
                      <div class="d-flex justify-content-center align-items-center" style="height:400px; background:black;">
                          <img src="<?= htmlspecialchars($p['imagen_principal']) ?>" class="content-media" style="max-height:100%; max-width:100%; object-fit: contain; cursor: pointer;" onclick="iniciarFullscreen('carouselProject<?= $p['id'] ?>', 0)">
                      </div>
                    </div>
                    
                    <?php 
                    $counter = 1;
                    if(!empty($imgs)): foreach($imgs as $img): ?>
                    <div class="carousel-item" data-index="<?= $counter ?>">
                        <div class="d-flex justify-content-center align-items-center" style="height:400px; background:black;">
                            <?php if($img['tipo'] == 'video'): ?>
                                <video src="<?= htmlspecialchars($img['ruta']) ?>" class="content-media" style="max-height:100%; max-width:100%;" controls></video>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($img['ruta']) ?>" class="content-media" style="max-height:100%; max-width:100%; object-fit: contain; cursor: pointer;" onclick="iniciarFullscreen('carouselProject<?= $p['id'] ?>', <?= $counter ?>)">
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $counter++; endforeach; endif; ?>
                  </div>
                  
                  <?php if(!empty($imgs)): ?>
                  <button class="carousel-control-prev" type="button" data-bs-target="#carouselProject<?= $p['id'] ?>" data-bs-slide="prev" style="width: 10%; opacity: 1;">
                    <span class="carousel-control-prev-icon bg-dark border border-secondary rounded-circle p-3 shadow" aria-hidden="true"></span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#carouselProject<?= $p['id'] ?>" data-bs-slide="next" style="width: 10%; opacity: 1;">
                    <span class="carousel-control-next-icon bg-dark border border-secondary rounded-circle p-3 shadow" aria-hidden="true"></span>
                  </button>
                  <?php endif; ?>

                  <button type="button" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 border border-secondary shadow" style="z-index: 2000;" title="Ver Pantalla Completa" onclick="botonExpandirClick('carouselProject<?= $p['id'] ?>'); event.preventDefault(); event.stopPropagation();">
                      <i class="fas fa-expand text-white"></i>
                  </button>
                </div>
                <div class="text-center mt-2 text-white-50 small"><i class="fas fa-hand-pointer me-1"></i> Toca la imagen o el botón <i class="fas fa-expand"></i> para ver Galería Gigante</div>
            </div>
            
            <div class="col-lg-5 d-flex flex-column">
                <div class="mb-4">
                    <h6 class="text-accent-orange fw-bold text-uppercase mb-3"><i class="fas fa-info-circle me-2"></i>Descripción</h6>
                    <p class="text-white-50" style="line-height: 1.8;"><?= nl2br(htmlspecialchars($p['descripcion_larga'])) ?></p>
                </div>

                <?php if(!empty($p['tecnologias'])): ?>
                <div class="mb-4">
                    <h6 class="text-accent-blue fw-bold text-uppercase mb-2"><i class="fas fa-code me-2"></i>Tecnologías</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach(explode(',', $p['tecnologias']) as $t): ?>
                            <span class="badge bg-secondary text-white border border-secondary px-3 py-2"><?= trim($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mt-auto d-grid gap-2">
                    <?php if(!empty($p['url_demo'])): ?>
                        <a href="<?= htmlspecialchars($p['url_demo']) ?>" target="_blank" class="btn btn-accent-blue btn-lg fw-bold shadow">
                            <i class="fas fa-external-link-alt me-2"></i> Ver Demo en Vivo
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar Ventana</button>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<div class="modal fade" id="modalFullscreenCarousel" tabindex="-1" style="z-index: 1060;" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen bg-black">
        <div class="modal-content bg-black">
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4" data-bs-dismiss="modal" style="z-index: 1080; filter: invert(1); opacity: 1; transform: scale(1.5);"></button>
            
            <div class="modal-body p-0 d-flex align-items-center justify-content-center w-100 h-100">
                <div id="fullscreenCarouselContainer" class="carousel slide w-100 h-100 d-flex align-items-center" data-bs-ride="false">
                    <div class="carousel-inner h-100" id="fullscreenCarouselInner">
                        </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#fullscreenCarouselContainer" data-bs-slide="prev" style="width: 5%;">
                        <span class="carousel-control-prev-icon p-4" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#fullscreenCarouselContainer" data-bs-slide="next" style="width: 5%;">
                        <span class="carousel-control-next-icon p-4" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTrailer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-black border border-secondary shadow-lg rounded-0">
            <div class="modal-header border-bottom border-secondary bg-darker">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-film text-accent-blue me-2"></i>Tráiler Oficial</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0 bg-black position-relative">
                <div class="ratio ratio-16x9">
                    <iframe id="iframeTrailer" src="" title="YouTube video trailer" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary bg-darker d-flex justify-content-between align-items-center">
                <small class="text-white-50"><i class="fas fa-broadcast-tower me-1"></i> Reproduciendo contenido multimedia</small>
                
                <a id="btnYoutubeLink" href="#" target="_blank" class="btn btn-danger fw-bold shadow-sm">
                    <i class="fab fa-youtube me-2"></i> Ver en YouTube
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// --- 1. SCRIPT DE GALERÍA (AMPLIAR) ---
function botonExpandirClick(sourceCarouselId) {
    var $activeItem = $('#' + sourceCarouselId + ' .carousel-item.active');
    var index = $activeItem.data('index') || 0;
    iniciarFullscreen(sourceCarouselId, index);
}

function iniciarFullscreen(sourceCarouselId, targetIndex) {
    var sourceContainer = document.getElementById(sourceCarouselId);
    var fullContainerInner = document.getElementById('fullscreenCarouselInner');
    fullContainerInner.innerHTML = '';
    
    var items = sourceContainer.querySelectorAll('.carousel-item');
    
    items.forEach(function(item, idx) {
        var mediaElement = item.querySelector('.content-media');
        if(mediaElement) {
            var src = mediaElement.getAttribute('src');
            var isVideo = (mediaElement.tagName === 'VIDEO');
            
            var newItem = document.createElement('div');
            newItem.className = 'carousel-item h-100' + (idx === targetIndex ? ' active' : '');
            
            var contentHTML = '';
            if(isVideo) {
                contentHTML = '<div class="d-flex justify-content-center align-items-center h-100"><video src="' + src + '" controls style="max-width:100%; max-height:100vh;"></video></div>';
            } else {
                contentHTML = '<div class="d-flex justify-content-center align-items-center h-100"><img src="' + src + '" style="max-width:100%; max-height:100vh; object-fit: contain;"></div>';
            }
            
            newItem.innerHTML = contentHTML;
            fullContainerInner.appendChild(newItem);
        }
    });
    
    var myModal = new bootstrap.Modal(document.getElementById('modalFullscreenCarousel'));
    myModal.show();
}

// --- 2. SCRIPT DE TRAILER YOUTUBE (NUEVO) ---
function abrirModalTrailer(url) {
    var videoId = "";
    // Expresión regular para sacar la ID de cualquier link de YouTube (corto o largo)
    var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    var match = url.match(regExp);
    
    if (match && match[2].length == 11) {
        videoId = match[2];
    } else {
        alert("La URL del video configurada en el panel no es válida.");
        return;
    }
    
    // Construimos la URL 'embed' con autoplay
    var embedUrl = "https://www.youtube.com/embed/" + videoId + "?autoplay=1&rel=0&modestbranding=1";
    
    // Asignamos al iframe y al botón
    document.getElementById('iframeTrailer').src = embedUrl;
    document.getElementById('btnYoutubeLink').href = url;
    
    // Mostramos el modal
    var myModal = new bootstrap.Modal(document.getElementById('modalTrailer'));
    myModal.show();
}

// Detener video al cerrar el modal (Limpiar src)
var modalTrailerEl = document.getElementById('modalTrailer');
if (modalTrailerEl) {
    modalTrailerEl.addEventListener('hidden.bs.modal', function () {
      document.getElementById('iframeTrailer').src = "";
    });
}
</script>

<?php include 'includes/footer.php'; ?>