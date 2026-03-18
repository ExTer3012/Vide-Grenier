<?php

namespace App\Models;

use App\Utility\Hash;
use Core\Model;
use Core\Error;
use Exception;

/**
 * Modèle User
 */
class User extends Model
{
    /**
     * Crée un utilisateur en base.
     *
     * @param array $data ['username', 'email', 'password' (déjà hashé)]
     * @return int ID du nouvel utilisateur
     * @throws Exception
     */
    public static function createUser(array $data): int
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ');

        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email',    $data['email']);
        $stmt->bindParam(':password', $data['password']);

        $stmt->execute();

        Error::log('INFO', "Nouvel utilisateur créé : " . $data['email']);

        return (int) $db->lastInsertId();
    }

    /**
     * Récupère un utilisateur par son email.
     *
     * @param string $email
     * @return array|false
     * @throws Exception
     */
    public static function getByEmail(string $email)
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT id, username, email, password, is_admin
            FROM users
            WHERE email = :email
            LIMIT 1
        ');

        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un email est déjà utilisé.
     *
     * @param string $email
     * @return bool
     * @throws Exception
     */
    public static function emailExists(string $email): bool
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT COUNT(id) FROM users WHERE email = :email
        ');

        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Met à jour le hash du mot de passe (rehash si nécessaire).
     *
     * @param int    $userId
     * @param string $newHash
     * @throws Exception
     */
    public static function updatePassword(int $userId, string $newHash): void
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            UPDATE users SET password = :password WHERE id = :id
        ');

        $stmt->bindParam(':password', $newHash);
        $stmt->bindParam(':id',       $userId, \PDO::PARAM_INT);
        $stmt->execute();
    }
}