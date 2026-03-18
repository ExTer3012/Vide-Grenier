<?php

namespace App\Utility;

/**
 * Utilitaire de hachage des mots de passe.
 * Utilise Argon2id (PASSWORD_ARGON2ID), recommandé par l'OWASP.
 * Le salt est géré automatiquement par PHP — plus besoin de le stocker.
 */
class Hash
{
    /**
     * Hache un mot de passe avec Argon2id.
     * Remplace l'ancien Hash::generate($password, $salt).
     *
     * @param string $password Mot de passe en clair
     * @return string          Hash Argon2id
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,  // 64 Mo
            'time_cost'   => 4,      // 4 itérations
            'threads'     => 1,
        ]);
    }

    /**
     * Vérifie un mot de passe contre un hash Argon2id.
     *
     * @param string $password Mot de passe en clair saisi par l'utilisateur
     * @param string $hash     Hash stocké en base
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Indique si le hash doit être recalculé (ex : changement des paramètres de coût).
     *
     * @param string $hash Hash stocké
     * @return bool
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 1,
        ]);
    }

    /**
     * Génère un token aléatoire sécurisé (ex : CSRF, remember-me).
     *
     * @param int $length Longueur en octets (le token hex sera 2x plus long)
     * @return string
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}