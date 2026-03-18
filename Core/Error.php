<?php

namespace Core;

use App\Config;

/**
 * Gestionnaire d'erreurs et d'exceptions.
 * Affiche les détails en dev, logue silencieusement en prod.
 */
class Error
{
    const LEVEL_ERROR   = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_INFO    = 2;
    const LEVEL_DEBUG   = 3;

    private static array $levelMap = [
        'ERROR'   => self::LEVEL_ERROR,
        'WARNING' => self::LEVEL_WARNING,
        'INFO'    => self::LEVEL_INFO,
        'DEBUG'   => self::LEVEL_DEBUG,
    ];

    public static function errorHandler(int $level, string $message, string $file, int $line): void
    {
        if (error_reporting() !== 0) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    public static function exceptionHandler(\Throwable $exception): void
    {
        $code = $exception->getCode();
        if ($code !== 404) {
            $code = 500;
        }
        http_response_code($code);

        if (Config::showErrors()) {
            echo '<h1>Erreur ' . $code . '</h1>';
            echo '<p><strong>Exception :</strong> ' . get_class($exception) . '</p>';
            echo '<p><strong>Message :</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
            echo '<p><strong>Fichier :</strong> ' . $exception->getFile() . ' ligne ' . $exception->getLine() . '</p>';
        } else {
            self::log(
                'ERROR',
                "Exception non catchee : " . get_class($exception)
                . " | Message : " . $exception->getMessage()
                . " | Fichier : " . $exception->getFile() . ":" . $exception->getLine()
            );

            View::renderTemplate($code . '.html');
        }
    }

    public static function log(string $level, string $message): void
    {
        $configuredLevel = self::$levelMap[Config::LOG_LEVEL] ?? self::LEVEL_ERROR;
        $messageLevel    = self::$levelMap[$level]            ?? self::LEVEL_ERROR;

        if ($messageLevel > $configuredLevel) {
            return;
        }

        $logDir  = dirname(__DIR__) . '/logs';
        $logFile = $logDir . '/' . date('Y-m-d') . '.log';

        $line = sprintf(
            "[%s] [%s] %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message
        );

        // Créer le dossier si absent
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        // Fallback vers le log PHP natif si le dossier n'est pas accessible en écriture
        if (!is_writable($logDir)) {
            error_log($line);
            return;
        }

        error_log($line, 3, $logFile);
    }
}