<?php
// Validar variables para evitar errores si no están definidas
if (!isset($pageTitle)) { $pageTitle = 'Federico González - Desarrollador & Escritor'; }
if (!isset($metaDescription)) { $metaDescription = 'Portfolio de desarrollo y universo literario de Federico González. Analista de Sistemas y Autor de Ciencia Ficción.'; }

// Definir URL canónica real (SEO CRÍTICO)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$currentUrl = $protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$domainUrl = "https://federicogonzalez.net"; // Tu dominio real
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="author" content="Federico González">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($currentUrl); ?>">

    <meta property="og:locale" content="es_AR">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="og:site_name" content="Federico González Portfolio">
    
    <meta property="og:image" content="<?php echo $domainUrl; ?>/assets/img/social-preview.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo $domainUrl; ?>/assets/img/social-preview.jpg">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Person",
      "name": "Federico González",
      "url": "https://federicogonzalez.net",
      "image": "https://federicogonzalez.net/assets/img/perfil.jpg",
      "jobTitle": "Analista de Sistemas & Escritor",
      "description": "Desarrollador Web Full Stack y Autor de novelas de ciencia ficción.",
      "sameAs": [
        "https://www.instagram.com/fedegonzalez.escritor",
        "https://www.linkedin.com/in/federicogonzalez" 
      ],
      "knowsAbout": ["PHP", "Desarrollo Web", "MySQL", "Escritura Creativa", "Ciencia Ficción"]
    }
    </script>

    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="preload" as="image" href="assets/img/hero.webp" type="image/webp">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-lg" id="mainNav">
    <div class="container">
        
        <style>
            /* Estilos incrustados del logo para asegurar que se vea bien */
            .brand-container { font-size: 1.9rem; }
            .brand-icon { font-size: 2rem; }
            .brand-pen { font-size: 1.4rem; }
            @media (max-width: 576px) {
                .brand-container { font-size: 1.4rem !important; }
                .brand-icon { font-size: 1.5rem !important; }
                .brand-pen { font-size: 1rem !important; }
                .navbar-brand { margin-right: 0 !important; }
            }
        </style>

        <a class="navbar-brand fw-bold d-flex align-items-center brand-container" href="index.php" style="font-family: 'Montserrat', sans-serif; letter-spacing: -1px; white-space: nowrap;">
            <div class="d-flex align-items-center me-3 fw-bold brand-icon" style="font-family: monospace; line-height: 1;">
                <span class="text-accent-blue">&lt;/</span>
                <i class="fas fa-pen-nib text-accent-orange mx-1 brand-pen" style="transform: rotate(15deg);"></i>
                <span class="text-accent-blue">&gt;</span>
            </div>
            <span class="text-accent-orange">FEDE</span>
            <span class="text-accent-blue">GONZÁLEZ</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto py-4 py-lg-0">
                <li class="nav-item"><a class="nav-link text-accent-blue" href="./index.php#portfolio">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link text-accent-orange" href="./index.php#writing">Escritura</a></li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link text-white dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Contenido
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="./index.php#podcast">Podcast</a></li>
                        <li><a class="dropdown-item" href="./index.php#history">Historia</a></li>
                        <li><a class="dropdown-item" href="./index.php#instagram">Instagram</a></li>
                    </ul>
                </li>
                
                <li class="nav-item"><a class="nav-link text-white" href="./index.php#about">Sobre Mí</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="./index.php#contact">Contacto</a></li>
            </ul>
        </div>
    </div>
</nav>

<div id="page-content">