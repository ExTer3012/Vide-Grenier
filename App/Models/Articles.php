<?php

namespace App\Models;

use Core\Model;
use Core\Error;
use DateTime;
use Exception;

/**
 * Modèle Articles
 */
class Articles extends Model
{
    /**
     * Retourne tous les articles avec filtre de tri optionnel.
     *
     * @param string $filter 'views' | 'date' | ''
     * @return array
     * @throws Exception
     */
    public static function getAll(string $filter = ''): array
    {
        $db = static::getDB();

        $sql = '
            SELECT a.id, a.name, a.description, a.published_date,
                   a.views, a.picture, a.user_id,
                   u.username
            FROM articles a
            INNER JOIN users u ON a.user_id = u.id
        ';

        switch ($filter) {
            case 'views':
                $sql .= ' ORDER BY a.views DESC';
                break;
            case 'date':  // Bug corrigé : était 'data'
                $sql .= ' ORDER BY a.published_date DESC';
                break;
        }

        $stmt = $db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne un article par son ID avec les infos de l'auteur.
     *
     * @param int $id
     * @return array|false
     * @throws Exception
     */
    public static function getOne(int $id)
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT a.id, a.name, a.description, a.published_date,
                   a.views, a.picture, a.user_id,
                   u.username, u.email
            FROM articles a
            INNER JOIN users u ON a.user_id = u.id
            WHERE a.id = :id
            LIMIT 1
        ');

        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Incrémente le compteur de vues d'un article.
     *
     * @param int $id
     * @throws Exception
     */
    public static function addOneView(int $id): void
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            UPDATE articles
            SET views = views + 1
            WHERE id = :id
        ');

        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Retourne les articles d'un utilisateur.
     *
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public static function getByUser(int $userId): array
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT a.id, a.name, a.description, a.published_date,
                   a.views, a.picture, a.user_id
            FROM articles a
            WHERE a.user_id = :user_id
            ORDER BY a.published_date DESC
        ');

        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne les 10 articles les plus récents (suggestions).
     *
     * @return array
     * @throws Exception
     */
    public static function getSuggest(): array
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT a.id, a.name, a.description, a.published_date,
                   a.views, a.picture, a.user_id,
                   u.username
            FROM articles a
            INNER JOIN users u ON a.user_id = u.id
            ORDER BY a.published_date DESC
            LIMIT 10
        ');

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Enregistre un nouvel article.
     *
     * @param array $data ['name', 'description', 'user_id']
     * @return int ID du nouvel article
     * @throws Exception
     */
    public static function save(array $data): int
    {
        $db   = static::getDB();
        $date = (new DateTime())->format('Y-m-d');

        $stmt = $db->prepare('
            INSERT INTO articles (name, description, user_id, published_date)
            VALUES (:name, :description, :user_id, :published_date)
        ');

        $stmt->bindParam(':name',           $data['name']);
        $stmt->bindParam(':description',    $data['description']);
        $stmt->bindParam(':user_id',        $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':published_date', $date);

        $stmt->execute();

        $id = (int) $db->lastInsertId();

        Error::log('INFO', "Nouvel article créé : ID={$id}, user_id={$data['user_id']}");

        return $id;
    }

    /**
     * Associe une image à un article.
     *
     * @param int    $articleId
     * @param string $pictureName
     * @throws Exception
     */
    public static function attachPicture(int $articleId, string $pictureName): void
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            UPDATE articles
            SET picture = :picture
            WHERE id = :id
        ');

        $stmt->bindParam(':picture', $pictureName);
        $stmt->bindParam(':id',      $articleId, \PDO::PARAM_INT);
        $stmt->execute();
    }
}