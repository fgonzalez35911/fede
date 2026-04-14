<?php
// Configuración
$directorio_imagenes = 'assets/img'; // La carpeta donde están tus fotos
$calidad_jpg = 75; // 75% es el equilibrio perfecto entre peso y calidad visual
$calidad_png = 8;  // Compresión PNG (0-9)

// Aumentamos el tiempo de ejecución y memoria por si hay imágenes muy pesadas
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

echo "<h1>Optimizador de Imágenes Automático</h1>";
echo "<p>Iniciando proceso en: <strong>/$directorio_imagenes</strong>...</p><hr>";

if (!is_dir($directorio_imagenes)) {
    die("<h3 style='color:red'>Error: La carpeta '$directorio_imagenes' no existe. Verifica la ruta.</h3>");
}

$archivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio_imagenes));
$contador = 0;
$ahorro_total = 0;

foreach ($archivos as $archivo) {
    if ($archivo->isDir()) continue;

    $ruta_completa = $archivo->getPathname();
    $extension = strtolower(pathinfo($ruta_completa, PATHINFO_EXTENSION));
    
    // Solo procesamos JPG, JPEG y PNG
    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) continue;

    // Tamaño original
    $peso_original = filesize($ruta_completa);
    
    // Si la imagen pesa menos de 150KB, la ignoramos para no perder calidad innecesaria
    if ($peso_original < 150000) {
        echo "<div style='color:gray'>[SALTADO] $ruta_completa (Ya es ligera)</div>";
        continue;
    }

    $imagen_optimizada = false;

    // Procesar JPG
    if ($extension == 'jpg' || $extension == 'jpeg') {
        $img = @imagecreatefromjpeg($ruta_completa);
        if ($img) {
            // Sobreescribimos la imagen con la nueva calidad
            imagejpeg($img, $ruta_completa, $calidad_jpg);
            imagedestroy($img);
            $imagen_optimizada = true;
        }
    }
    // Procesar PNG
    elseif ($extension == 'png') {
        $img = @imagecreatefrompng($ruta_completa);
        if ($img) {
            // Mantenemos transparencia
            imagealphablending($img, false);
            imagesavealpha($img, true);
            // Sobreescribimos
            imagepng($img, $ruta_completa, $calidad_png);
            imagedestroy($img);
            $imagen_optimizada = true;
        }
    }

    if ($imagen_optimizada) {
        // Limpiamos la caché de estado de archivo
        clearstatcache();
        $peso_nuevo = filesize($ruta_completa);
        $ahorro = $peso_original - $peso_nuevo;
        $ahorro_total += $ahorro;
        
        // Convertir a visualización legible (KB/MB)
        $peso_orig_fmt = number_format($peso_original / 1024, 2);
        $peso_nuev_fmt = number_format($peso_nuevo / 1024, 2);
        
        echo "<div style='color:green'><strong>[OPTIMIZADO]</strong> $ruta_completa <br>";
        echo "Antes: {$peso_orig_fmt} KB -> Ahora: {$peso_nuev_fmt} KB</div>";
        $contador++;
    } else {
        echo "<div style='color:red'>[ERROR] No se pudo procesar: $ruta_completa</div>";
    }
    
    // Forzamos la salida al navegador para que veas el progreso
    flush(); 
}

echo "<hr>";
echo "<h3>¡Proceso Finalizado!</h3>";
echo "<p>Imágenes optimizadas: <strong>$contador</strong></p>";
echo "<p>Espacio ahorrado en disco: <strong>" . number_format($ahorro_total / 1024 / 1024, 2) . " MB</strong></p>";
?>
