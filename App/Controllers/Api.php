<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Models\Cities;
use Core\Error;
use Exception;

/**
 * Contrôleur API — endpoints JSON
 */
class Api extends \Core\Controller
{
    /**
     * GET /api/products?sort=views|date
     * Retourne la liste des articles en JSON.
     */
    public function productsAction(): void
    {
        header('Content-Type: application/json');

        $allowed = ['views', 'date', ''];
        $sort    = $_GET['sort'] ?? '';

        if (!in_array($sort, $allowed, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètre sort invalide. Valeurs acceptées : views, date.']);
            return;
        }

        try {
            $articles = Articles::getAll($sort);
            echo json_encode($articles);
        } catch (Exception $e) {
            Error::log('ERROR', "API /products : " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur.']);
        }
    }

    /**
     * GET /api/cities?query=Paris
     * Retourne les villes correspondant à la recherche.
     */
    public function citiesAction(): void
    {
        header('Content-Type: application/json');

        $query = trim($_GET['query'] ?? '');

        if (empty($query) || strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        // Protection XSS basique sur le paramètre
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

        try {
            $cities = Cities::search($query);
            echo json_encode($cities);
        } catch (Exception $e) {
            Error::log('ERROR', "API /cities : " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur.']);
        }
    }
}