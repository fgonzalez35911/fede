<?php
// mi-portfolio/includes/podcast_episodes.php

// URL de tu RSS Feed (confirmada)
$rss_feed_url = 'https://anchor.fm/s/1027977fc/podcast/rss'; 

// Intentar cargar el feed RSS
$rss = @simplexml_load_file($rss_feed_url, 'SimpleXMLElement', LIBXML_NOCDATA); 

if ($rss === false) {
    echo "<p class='text-danger text-center'>No se pudo cargar el feed RSS del podcast. Verifica la URL o el estado del feed.</p>";
} else {
    $count = 0;
    foreach ($rss->channel->item as $item) {
        if ($count >= 3) break; // Mostrar solo los 3 últimos episodios

        $title = htmlspecialchars((string)$item->title);
        $link = htmlspecialchars((string)$item->link); 
        
        // Obtener descripción y acortarla
        $description = '';
        if (isset($item->description)) {
            $description = htmlspecialchars(strip_tags((string)$item->description));
        } elseif (isset($item->children('itunes', true)->summary)) {
             $description = htmlspecialchars(strip_tags((string)$item->children('itunes', true)->summary));
        }
        $short_description = (strlen($description) > 120) ? substr($description, 0, 117) . '...' : $description;

        $pubDate = isset($item->pubDate) ? date('d M Y', strtotime((string)$item->pubDate)) : '';

        // --- RENDERIZADO DEL NUEVO DISEÑO (3 COLUMNAS REDONDEADAS/CIRCULARES) ---
        echo "
        <div class='col'>
            <div class='podcast-episode-widget h-100 p-4 d-flex flex-column justify-content-between text-center'>
                <i class='fas fa-podcast fa-3x text-accent-orange mb-3'></i>
                <h5 class='text-accent-blue mb-2'>" . $title . "</h5>
                <p class='text-white mb-3 flex-grow-1'>" . $short_description . "</p>
                <div>
                    <small class='text-white-50 d-block mb-3'>Publicado: " . $pubDate . "</small>
                    <a href='" . $link . "' target='_blank' class='btn btn-sm btn-accent-blue-outline'><i class='fas fa-play-circle me-2'></i> Escuchar</a>
                </div>
            </div>
        </div>";
        $count++;
    }
}
?>