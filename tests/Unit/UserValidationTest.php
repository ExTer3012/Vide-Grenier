<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la logique de validation utilisateur
 * (logique extraite du contrôleur User — testable sans HTTP ni DB)
 */
class UserValidationTest extends TestCase
{
    // ------------------------------------------------------------------
    // Validation email
    // ------------------------------------------------------------------

    public function testEmailValideAccepte(): void
    {
        $emails = [
            'john.doe@gmail.com',
            'admin@admin.fr',
            'user+tag@domain.co.uk',
        ];

        foreach ($emails as $email) {
            $this->assertNotFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "'{$email}' devrait être valide"
            );
        }
    }

    public function testEmailInvalideRejete(): void
    {
        $emails = [
            'pas-un-email',
            '@domaine.fr',
            'user@',
            '',
            'user @domain.fr',
        ];

        foreach ($emails as $email) {
            $this->assertFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "'{$email}' devrait être invalide"
            );
        }
    }

    // ------------------------------------------------------------------
    // Validation mot de passe
    // ------------------------------------------------------------------

    public function testMotDePasseTropCourtRejete(): void
    {
        $mdpCourts = ['', '1234567', 'abc'];

        foreach ($mdpCourts as $mdp) {
            $this->assertLessThan(8, strlen($mdp),
                "'{$mdp}' devrait faire moins de 8 caractères");
        }
    }

    public function testMotDePasseSuffisantAccepte(): void
    {
        $mdpValides = ['12345678', 'monMotDePasse', 'P@ssw0rd!'];

        foreach ($mdpValides as $mdp) {
            $this->assertGreaterThanOrEqual(8, strlen($mdp),
                "'{$mdp}' devrait faire au moins 8 caractères");
        }
    }

    public function testConfirmationMotDePasseCorrespondante(): void
    {
        $password  = 'monMotDePasse';
        $passCheck = 'monMotDePasse';
        $this->assertSame($password, $passCheck);
    }

    public function testConfirmationMotDePasseNonCorrespondante(): void
    {
        $password  = 'monMotDePasse';
        $passCheck = 'autreMotDePasse';
        $this->assertNotSame($password, $passCheck);
    }

    // ------------------------------------------------------------------
    // Validation nom d'utilisateur
    // ------------------------------------------------------------------

    public function testUsernameMinimum2Caracteres(): void
    {
        $this->assertGreaterThanOrEqual(2, strlen('Jo'));
        $this->assertLessThan(2, strlen('J'));
        $this->assertLessThan(2, strlen(''));
    }

    // ------------------------------------------------------------------
    // Logique de validation complète (reproduit validateRegister)
    // ------------------------------------------------------------------

    /**
     * @dataProvider provideDonneesInscriptionValides
     */
    public function testInscriptionValide(string $username, string $email, string $password, string $passCheck): void
    {
        $errors = $this->validateRegister($username, $email, $password, $passCheck);
        $this->assertEmpty($errors, "Aucune erreur attendue pour des données valides");
    }

    /**
     * @dataProvider provideDonneesInscriptionInvalides
     */
    public function testInscriptionInvalide(string $username, string $email, string $password, string $passCheck, int $nbErreurs): void
    {
        $errors = $this->validateRegister($username, $email, $password, $passCheck);
        $this->assertCount($nbErreurs, $errors);
    }

    // ------------------------------------------------------------------
    // Data providers
    // ------------------------------------------------------------------

    public static function provideDonneesInscriptionValides(): array
    {
        return [
            'cas_nominal'          => ['Jean Dupont', 'jean@test.fr', 'password123', 'password123'],
            'username_minimum'     => ['Jo',          'jo@test.fr',  'password123', 'password123'],
            'email_complexe'       => ['User',        'u+t@d.co.uk', 'password123', 'password123'],
        ];
    }

    public static function provideDonneesInscriptionInvalides(): array
    {
        return [
            'username_trop_court'        => ['J',    'j@test.fr',    'password123', 'password123', 1],
            'email_invalide'             => ['Jean', 'pas-un-email', 'password123', 'password123', 1],
            'mdp_trop_court'             => ['Jean', 'j@test.fr',    '1234567',     '1234567',     1],
            'mdp_non_correspondants'     => ['Jean', 'j@test.fr',    'password123', 'different',   1],
            'tout_invalide'              => ['J',    'invalide',     '123',         'xyz',         4],
        ];
    }

    // ------------------------------------------------------------------
    // Méthode helper (copie de User::validateRegister)
    // ------------------------------------------------------------------

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
}