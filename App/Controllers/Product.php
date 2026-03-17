<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Utility\Csrf;
use App\Utility\Upload;
use Core\Error;
use Core\View;
use Exception;

/**
 * Contrôleur Product — ajout et affichage d'articles
 */
class Product extends \Core\Controller
{
    /**
     * Affiche et traite le formulaire d'ajout d'article.
     */
    public function indexAction(): void
    {
        if (isset($_POST['submit'])) {
            // Validation CSRF
            if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                Error::log('WARNING', "Tentative CSRF sur /product depuis IP: " . ($_SERVER['REMOTE_ADDR'] ?? ''));
                View::renderTemplate('Product/Add.html', [
                    'error'      => 'Requête invalide, veuillez réessayer.',
                    'csrf_token' => Csrf::getToken(),
                ]);
                return;
            }

            $name        = trim($_POST['name']        ?? '');
            $description = trim($_POST['description'] ?? '');

            $errors = [];

            if (empty($name) || strlen($name) < 3) {
                $errors[] = 'Le titre doit faire au moins 3 caractères.';
            }

            if (empty($description)) {
                $errors[] = 'La description est obligatoire.';
            }

            if (!empty($errors)) {
                View::renderTemplate('Product/Add.html', [
                    'errors'      => $errors,
                    'old'         => ['name' => $name, 'description' => $description],
                    'csrf_token'  => Csrf::getToken(),
                ]);
                return;
            }

            try {
                $id = Articles::save([
                    'name'        => $name,
                    'description' => $description,
                    'user_id'     => $_SESSION['user']['id'],
                ]);

                if (!empty($_FILES['picture']['name'])) {
                    $pictureName = Upload::uploadFile($_FILES['picture'], $id);
                    Articles::attachPicture($id, $pictureName);
                }

                Csrf::regenerate();
                header('Location: /product/' . $id);
                exit;

            } catch (Exception $e) {
                Error::log('ERROR', "Erreur ajout article : " . $e->getMessage());
                View::renderTemplate('Product/Add.html', [
                    'error'      => 'Une erreur est survenue lors de l\'enregistrement.',
                    'csrf_token' => Csrf::getToken(),
                ]);
            }

            return;
        }

        View::renderTemplate('Product/Add.html', [
            'csrf_token' => Csrf::getToken(),
        ]);
    }

    /**
     * Affiche la page d'un produit.
     */
    public function showAction(): void
    {
        $id = (int) $this->route_params['id'];

        try {
            Articles::addOneView($id);
            $article     = Articles::getOne($id);
            $suggestions = Articles::getSuggest();

        } catch (Exception $e) {
            Error::log('ERROR', "Erreur showAction article ID={$id} : " . $e->getMessage());
            http_response_code(500);
            View::renderTemplate('500.html');
            return;
        }

        if (!$article) {
            http_response_code(404);
            View::renderTemplate('404.html');
            return;
        }

        View::renderTemplate('Product/Show.html', [
            'article'     => $article,
            'suggestions' => $suggestions,
        ]);
    }
}