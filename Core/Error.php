<?php

namespace Core;

use App\Config;

/**
 * Gestionnaire d'erreurs et d'exceptions.
 * Affiche les détails en dev, logue silencieusement en prod.
 */
class Error
{
    // Niveaux de log (ordre croissant de verbosité)
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

    /**
     * Convertit les erreurs PHP en exceptions.
     */
    public static function errorHandler(int $level, string $message, string $file, int $line): void
    {
        if (error_reporting() !== 0) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Gère les exceptions non catchées.
     */
    public static function exceptionHandler(\Throwable $exception): void
    {
        $code = $exception->getCode();
        if ($code !== 404) {
            $code = 500;
        }
        http_response_code($code);

        if (Config::showErrors()) {
            // Mode développement : affichage détaillé
            echo '<h1>Erreur ' . $code . '</h1>';
            echo '<p><strong>Exception :</strong> ' . get_class($exception) . '</p>';
            echo '<p><strong>Message :</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
            echo '<p><strong>Fichier :</strong> ' . $exception->getFile() . ' ligne ' . $exception->getLine() . '</p>';
        } else {
            // Mode production : log fichier, page d'erreur générique
            self::log(
                'ERROR',
                "Exception non catchée : " . get_class($exception)
                . " | Message : " . $exception->getMessage()
                . " | Fichier : " . $exception->getFile() . ":" . $exception->getLine()
                . " | Trace : " . $exception->getTraceAsString()
            );

            View::renderTemplate($code . '.html');
        }
    }

    /**
     * Enregistre un message dans le fichier de log si le niveau est suffisant.
     *
     * @param string $level   ERROR | WARNING | INFO | DEBUG
     * @param string $message Message à logger
     */
    public static function log(string $level, string $message): void
    {
        $configuredLevel = self::$levelMap[Config::LOG_LEVEL] ?? self::LEVEL_ERROR;
        $messageLevel    = self::$levelMap[$level]            ?? self::LEVEL_ERROR;

        // On ne logue que si le niveau du message est <= au niveau configuré
        if ($messageLevel > $configuredLevel) {
            return;
        }

        $logDir  = dirname(__DIR__) . '/logs';
        $logFile = $logDir . '/' . date('Y-m-d') . '.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $line = sprintf(
            "[%s] [%s] %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message
        );

        error_log($line, 3, $logFile);
    }
}