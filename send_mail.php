<?php
// mi-portfolio/send_mail.php

// --- CONFIGURACIÓN OBLIGATORIA ---
$recipient_email = "info@federicogonzalez.net"; 

// AÑADIDO: Este será el email "De" que verá el receptor.
// Usa un email que exista en tu hosting.
$sender_email = "info@federicogonzalez.net";
// ---------------------------------

// Configura la respuesta como JSON
header('Content-Type: application/json');

// Función para devolver una respuesta JSON y terminar el script
function send_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// 1. Verificar que el método sea POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    send_response(false, 'Método no permitido.');
}

// --- AÑADIDO: Verificación de Honeypot (Anti-Spam) ---
// Este campo debe estar vacío. Si tiene algo, es un bot.
if (!empty($_POST['website_url'])) {
    // Engañamos al bot haciéndole creer que todo salió bien
    send_response(true, 'Mensaje enviado correctamente.');
}
// --- FIN Anti-Spam ---


// 2. Limpiar y validar los datos del formulario
$name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// Validaciones básicas
if (empty($name) || empty($email) || empty($message)) {
    send_response(false, 'Todos los campos son obligatorios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_response(false, 'La dirección de email no es válida.');
}

// 3. Construir el correo
$subject = "Nuevo Mensaje de tu Portfolio de: $name";

$email_content = "Has recibido un nuevo mensaje desde tu formulario de contacto web.\n\n";
$email_content .= "Nombre: $name\n";
$email_content .= "Email: $email\n\n";
$email_content .= "Mensaje:\n$message\n";

// 4. Construir las cabeceras del correo
// MODIFICADO: Esta es la forma profesional de hacerlo
$headers = "From: $name <$sender_email>\r\n"; // Usa tu email profesional
$headers .= "Reply-To: $email\r\n"; // El email del visitante va aquí
$headers .= "X-Mailer: PHP/" . phpversion();

// 5. Enviar el correo
if (mail($recipient_email, $subject, $email_content, $headers)) {
    send_response(true, 'Mensaje enviado correctamente.');
} else {
    send_response(false, 'El servidor no pudo enviar el mensaje. Inténtalo más tarde.');
}
?>