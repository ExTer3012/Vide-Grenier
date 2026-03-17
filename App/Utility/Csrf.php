<?php

namespace App\Utility;

/**
 * Protection CSRF (Cross-Site Request Forgery).
 * Génère et valide un token dans la session.
 */
class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    /**
     * Retourne le token CSRF courant, en génère un si absent.
     */
    public static function getToken(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = Hash::generateToken(32);
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Valide le token soumis via POST.
     *
     * @param string|null $token Token reçu du formulaire
     * @return bool
     */
    public static function validate(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        // hash_equals évite les timing attacks
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Génère le champ HTML caché à insérer dans chaque formulaire.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8')
            . '">';
    }

    /**
     * Renouvelle le token (à appeler après une action sensible).
     */
    public static function regenerate(): void
    {
        $_SESSION[self::SESSION_KEY] = Hash::generateToken(32);
    }
}