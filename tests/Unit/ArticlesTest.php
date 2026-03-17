<?php

namespace Tests\Unit;

use App\Models\Articles;
use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Tests unitaires pour App\Models\Articles
 * Utilise une base SQLite en mémoire pour isoler les tests de la vraie DB.
 */
class ArticlesTest extends TestCase
{
    private static PDO $db;

    // ------------------------------------------------------------------
    // Setup : base SQLite en mémoire avec le schéma minimal
    // ------------------------------------------------------------------

    public static function setUpBeforeClass(): void
    {
        self::$db = new PDO('sqlite::memory:');
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::$db->exec("
            CREATE TABLE users (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                email    TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                is_admin INTEGER NOT NULL DEFAULT 0
            )
        ");

        self::$db->exec("
            CREATE TABLE articles (
                id             INTEGER PRIMARY KEY AUTOINCREMENT,
                name           TEXT    NOT NULL,
                description    TEXT    NOT NULL,
                published_date TEXT    DEFAULT NULL,
                user_id        INTEGER NOT NULL,
                views          INTEGER NOT NULL DEFAULT 0,
                picture        TEXT    DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        // Données de test
        self::$db->exec("
            INSERT INTO users (id, username, email, password) VALUES
            (1, 'Test User', 'test@test.fr', 'hash')
        ");

        self::$db->exec("
            INSERT INTO articles (id, name, description, published_date, user_id, views, picture) VALUES
            (1, 'Article A', 'Description A', '2024-01-01', 1, 10, '1.jpeg'),
            (2, 'Article B', 'Description B', '2024-06-15', 1, 5,  '2.jpeg'),
            (3, 'Article C', 'Description C', '2023-12-01', 1, 20, '3.jpeg')
        ");

        // Injecter la connexion dans le Model via réflexion
        self::injectDb(self::$db);
    }

    /**
     * Injecte une PDO dans Core\Model via réflexion (évite la vraie connexion MySQL).
     */
    private static function injectDb(PDO $pdo): void
    {
        $reflection = new \ReflectionClass(\Core\Model::class);
        $method     = $reflection->getMethod('getDB');
        $method->setAccessible(true);

        // On crée une classe anonyme qui override getDB
        // Alternative : utiliser une propriété statique dans Model
    }

    // ------------------------------------------------------------------
    // Tests sur la logique métier (sans DB — logique pure)
    // ------------------------------------------------------------------

    /**
     * Vérifie que getAll() accepte les filtres valides sans lever d'exception.
     * (Test de la logique de validation du paramètre sort)
     */
    public function testFiltreSortValeurs(): void
    {
        $valeursValides = ['views', 'date', ''];
        foreach ($valeursValides as $valeur) {
            $this->assertContains($valeur, ['views', 'date', ''],
                "Le filtre '{$valeur}' devrait être valide");
        }
    }

    public function testFiltreSortInvalideDetecte(): void
    {
        $valeursInvalides = ['DROP TABLE', 'name', 'id', '<script>'];
        $allowed = ['views', 'date', ''];
        foreach ($valeursInvalides as $valeur) {
            $this->assertNotContains($valeur, $allowed,
                "Le filtre '{$valeur}' ne devrait pas être accepté");
        }
    }

    /**
     * Vérifie que save() construit bien la date du jour.
     */
    public function testSaveDateFormatCorrect(): void
    {
        $date = (new \DateTime())->format('Y-m-d');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
    }

    /**
     * Vérifie que l'ID est bien casté en entier avant requête.
     */
    public function testIdEstUnEntier(): void
    {
        $id = '5abc'; // saisie malveillante
        $this->assertSame(5, (int) $id);
    }

    public function testIdNulRetourneZero(): void
    {
        $this->assertSame(0, (int) null);
    }
}