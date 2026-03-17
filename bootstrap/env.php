<?php

/**
 * Chargeur de variables d'environnement
 * sert surtout pour le dev
 * sans Docker (Apache classique).
 */

$env = getenv('APP_ENV') ?: 'dev';
$envFiles = [
    dirname(__DIR__) . "/.env.{$env}",
    dirname(__DIR__) . '/.env',
];

foreach ($envFiles as $file) {
    if (!file_exists($file)) {
        continue;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);

        // Ne pas écraser une variable déjà définie (Docker l'a déjà injectée)
        if (!getenv($name)) {
            putenv("{$name}={$value}");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
    break; // On prend le premier fichier trouvé
}

// Définir les constantes utilisées par Config.php
define('DB_HOST',          getenv('DB_HOST')          ?: 'localhost');
define('DB_NAME',          getenv('DB_NAME')          ?: 'videgrenierenligne');
define('DB_USER',          getenv('DB_USER')          ?: 'root');
define('DB_PASSWORD',      getenv('DB_PASSWORD')      ?: '');
define('APP_ENV',          getenv('APP_ENV')          ?: 'dev');
define('APP_DEBUG',        getenv('APP_DEBUG')        ?: 'false');
define('LOG_LEVEL',        getenv('LOG_LEVEL')        ?: 'ERROR');
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 3600);
define('SESSION_SECURE',   getenv('SESSION_SECURE')   ?: 'false');
define('UPLOAD_MAX_SIZE',  getenv('UPLOAD_MAX_SIZE')  ?: 5242880);
define('UPLOAD_PATH',      getenv('UPLOAD_PATH')      ?: __DIR__ . '/../public/storage');