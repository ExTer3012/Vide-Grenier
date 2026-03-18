<?php

namespace App\Controllers;

use App\Models\Articles;
use Core\Error;
use Core\View;

/**
 * Contrôleur Home
 */
class Home extends \Core\Controller
{
    /**
     * Affiche la page d'accueil
     */
    public function indexAction(): void
    {
        try {
            $total = Articles::countAll();
        } catch (\Exception $e) {
            Error::log('ERROR', "Erreur indexAction : " . $e->getMessage());
            $total = 0;
        }

        View::renderTemplate('Home/index.html', [
            'total_articles' => $total,
        ]);
    }
}