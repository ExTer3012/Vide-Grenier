<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Models\User as UserModel;
use App\Utility\Csrf;
use App\Utility\Hash;
use Core\Error;
use Core\View;
use Exception;

/**
 * Contrôleur User — login, register, account, logout
 */
class User extends \Core\Controller
{
    /**
     * Affiche et traite le formulaire de connexion.
     */
    public function loginAction(): void
    {
        if (isset($_POST['submit'])) {
            // Validation CSRF
            if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                Error::log('WARNING', "Tentative CSRF sur /login depuis IP: " . ($_SERVER['REMOTE_ADDR'] ?? ''));
                $this->redirectWithError('/login', 'Requête invalide, veuillez réessayer.');
                return;
            }

            $email    = trim($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validation basique
            if (empty($email) || empty($password)) {
                View::renderTemplate('User/login.html', [
                    'error'      => 'Email et mot de passe obligatoires.',
                    'csrf_token' => Csrf::getToken(),
                ]);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                View::renderTemplate('User/login.html', [
                    'error'      => 'Format d\'email invalide.',
                    'csrf_token' => Csrf::getToken(),
                ]);
                return;
            }

            try {
                $success = $this->login($email, $password);

                if ($success) {
                    Csrf::regenerate();
                    header('Location: /account');
                    exit;
                }

                View::renderTemplate('User/login.html', [
                    'error'      => 'Email ou mot de passe incorrect.',
                    'csrf_token' => Csrf::getToken(),
                ]);

            } catch (Exception $e) {
                Error::log('ERROR', "Erreur login : " . $e->getMessage());
                View::renderTemplate('User/login.html', [
                    'error'      => 'Une erreur est survenue, veuillez réessayer.',
                    'csrf_token' => Csrf::getToken(),
                ]);
            }

            return;
        }

        View::renderTemplate('User/login.html', [
            'csrf_token' => Csrf::getToken(),
        ]);
    }

    /**
     * Affiche et traite le formulaire d'inscription.
     */
    public function registerAction(): void
    {
        if (isset($_POST['submit'])) {
            // Validation CSRF
            if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                Error::log('WARNING', "Tentative CSRF sur /register depuis IP: " . ($_SERVER['REMOTE_ADDR'] ?? ''));
                $this->redirectWithError('/register', 'Requête invalide, veuillez réessayer.');
                return;
            }

            $username  = trim($_POST['username']       ?? '');
            $email     = trim($_POST['email']          ?? '');
            $password  = trim($_POST['password']       ?? '');
            $passCheck = trim($_POST['password-check'] ?? '');

            $errors = $this->validateRegister($username, $email, $password, $passCheck);

            if (!empty($errors)) {
                View::renderTemplate('User/register.html', [
                    'errors'     => $errors,
                    'old'        => ['username' => $username, 'email' => $email],
                    'csrf_token' => Csrf::getToken(),
                ]);
                return;
            }

            try {
                if (UserModel::emailExists($email)) {
                    View::renderTemplate('User/register.html', [
                        'errors'     => ['Cet email est déjà utilisé.'],
                        'old'        => ['username' => $username, 'email' => $email],
                        'csrf_token' => Csrf::getToken(),
                    ]);
                    return;
                }

                $userId = UserModel::createUser([
                    'email'    => $email,
                    'username' => $username,
                    'password' => Hash::hashPassword($password),
                ]);

                // Connexion automatique après inscription
                $this->login($email, $password);
                Csrf::regenerate();

                header('Location: /account');
                exit;

            } catch (Exception $e) {
                Error::log('ERROR', "Erreur register : " . $e->getMessage());
                View::renderTemplate('User/register.html', [
                    'errors'     => ['Une erreur est survenue, veuillez réessayer.'],
                    'old'        => ['username' => $username, 'email' => $email],
                    'csrf_token' => Csrf::getToken(),
                ]);
            }

            return;
        }

        View::renderTemplate('User/register.html', [
            'csrf_token' => Csrf::getToken(),
        ]);
    }

    /**
     * Affiche le compte de l'utilisateur connecté.
     */
    public function accountAction(): void
    {
        try {
            $articles = Articles::getByUser($_SESSION['user']['id']);
        } catch (Exception $e) {
            Error::log('ERROR', "Erreur accountAction : " . $e->getMessage());
            $articles = [];
        }

        View::renderTemplate('User/account.html', [
            'articles' => $articles,
        ]);
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logoutAction(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        header('Location: /');
        exit;
    }

    // -------------------------------------------------------------------------
    // Méthodes privées
    // -------------------------------------------------------------------------

    /**
     * Authentifie un utilisateur et initialise la session.
     *
     * @return bool true si succès
     * @throws Exception
     */
    private function login(string $email, string $password): bool
    {
        $user = UserModel::getByEmail($email);

        if (!$user) {
            return false;
        }

        if (!Hash::verifyPassword($password, $user['password'])) {
            Error::log('WARNING', "Échec de connexion pour : {$email}");
            return false;
        }

        // Rehash si les paramètres Argon2 ont changé
        if (Hash::needsRehash($user['password'])) {
            UserModel::updatePassword($user['id'], Hash::hashPassword($password));
            Error::log('INFO', "Rehash du mot de passe pour : {$email}");
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'is_admin' => (bool) $user['is_admin'],
        ];

        Error::log('INFO', "Connexion réussie : {$email}");

        return true;
    }

    /**
     * Valide les champs du formulaire d'inscription.
     *
     * @return string[] Liste d'erreurs (vide si OK)
     */
    private function validateRegister(string $username, string $email, string $password, string $passCheck): array
    {
        $errors = [];

        if (empty($username) || strlen($username) < 2) {
            $errors[] = 'Le nom d\'utilisateur doit faire au moins 2 caractères.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format d\'email invalide.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit faire au moins 8 caractères.';
        }

        if ($password !== $passCheck) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        return $errors;
    }

    /**
     * Redirige avec un message d'erreur en session flash.
     */
    private function redirectWithError(string $url, string $message): void
    {
        $_SESSION['flash_error'] = $message;
        header('Location: ' . $url);
        exit;
    }
}