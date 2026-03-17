<?php

namespace App;

/**
 * Application configuration
 * Lit la configuration depuis les variables d'environnement.
 * Ne jamais mettre de valeurs en dur dans ce fichier.
 */
class Config
{
    /** Base de données */
    const DB_HOST     = DB_HOST;
    const DB_NAME     = DB_NAME;
    const DB_USER     = DB_USER;
    const DB_PASSWORD = DB_PASSWORD;

    /** Environnement applicatif */
    const APP_ENV   = APP_ENV;
    const APP_DEBUG = APP_DEBUG;

    /** Niveau de log : ERROR, WARNING, INFO, DEBUG */
    const LOG_LEVEL = LOG_LEVEL;

    /** Session */
    const SESSION_LIFETIME = SESSION_LIFETIME;
    const SESSION_SECURE   = SESSION_SECURE;

    /** Upload */
    const UPLOAD_MAX_SIZE = UPLOAD_MAX_SIZE;
    const UPLOAD_PATH     = UPLOAD_PATH;

    /**
     * Lit une variable d'environnement avec valeur par défaut.
     */
    public static function env(string $key, $default = null)
    {
        $value = getenv($key);
        return ($value !== false) ? $value : $default;
    }

    /**
     * Retourne true si l'application est en mode développement.
     */
    public static function isDev(): bool
    {
        return self::env('APP_ENV', 'prod') === 'dev';
    }

    /**
     * Retourne true si les erreurs doivent être affichées.
     */
    public static function showErrors(): bool
    {
        return filter_var(self::env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}