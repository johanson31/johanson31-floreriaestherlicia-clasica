<?php

/*
 * Copiar como config/config.php y ajustar. config.php no se sube a git.
 * No hay contraseñas: la web solo lee la API pública de Laravel.
 */
return [
    // Laravel (admin + API). Las fotos de productos también se sirven desde aquí.
    'api_url' => 'https://admin.floreriaestherlicia.com',

    // Minutos que se guarda cada respuesta de la API.
    'cache_minutos' => 5,

    // Respaldo si el admin no tiene configurado "whatsapp_contacto" (solo dígitos, con código de país).
    'whatsapp' => '51999999999',

    // Mostrar detalles de errores (solo en local).
    'debug' => false,
];
