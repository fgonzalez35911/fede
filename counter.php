<?php
$count_file = 'contador.txt';
$ip_file = 'ips.txt';
$timeout = 60 * 60 * 24; // 24 horas en segundos

$current_ip = $_SERVER['REMOTE_ADDR'];
$current_time = time();

$ips = [];
if (file_exists($ip_file)) {
    // Leer el archivo de IPs
    $ips_data = file_get_contents($ip_file);
    if ($ips_data) {
        $ips = json_decode($ips_data, true);
    }
    if (!is_array($ips)) { $ips = []; }
}

// 1. Limpiar IPs viejas
foreach ($ips as $ip => $time) {
    if ($current_time - $time > $timeout) {
        unset($ips[$ip]);
    }
}

// 2. Si la IP del visitante actual NO está en la lista (o ya expiró)
if (!isset($ips[$current_ip])) {
    // Añadir la IP actual a la lista
    $ips[$current_ip] = $current_time;
    
    // Incrementar el contador principal
    $count = 1;
    if (file_exists($count_file)) {
        $count = (int)file_get_contents($count_file);
        $count++;
    }
    // Guardar el nuevo contador
    file_put_contents($count_file, $count, LOCK_EX);
    
    // Guardar la lista de IPs actualizada
    file_put_contents($ip_file, json_encode($ips), LOCK_EX);
}

// 3. Mostrar el conteo actual
$count_to_display = file_exists($count_file) ? (int)file_get_contents($count_file) : 0;
echo number_format($count_to_display, 0, ',', '.');
?>