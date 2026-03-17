<?php

namespace Tests\Unit;

use App\Utility\Csrf;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour App\Utility\Csrf
 */
class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        // Initialise une session propre avant chaque test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['csrf_token']);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['csrf_token']);
    }

    // ------------------------------------------------------------------
    // getToken
    // ------------------------------------------------------------------

    public function testGetTokenGenereUnTokenSiAbsent(): void
    {
        $token = Csrf::getToken();
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    public function testGetTokenRetourneToujoursLeMemeToken(): void
    {
        $token1 = Csrf::getToken();
        $token2 = Csrf::getToken();
        $this->assertSame($token1, $token2);
    }

    public function testGetTokenEstStockeEnSession(): void
    {
        $token = Csrf::getToken();
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    // ------------------------------------------------------------------
    // validate
    // ------------------------------------------------------------------

    public function testValidateAvecTokenCorrect(): void
    {
        $token = Csrf::getToken();
        $this->assertTrue(Csrf::validate($token));
    }

    public function testValidateAvecTokenIncorrect(): void
    {
        Csrf::getToken();
        $this->assertFalse(Csrf::validate('token_invalide'));
    }

    public function testValidateAvecTokenNull(): void
    {
        Csrf::getToken();
        $this->assertFalse(Csrf::validate(null));
    }

    public function testValidateAvecTokenVide(): void
    {
        Csrf::getToken();
        $this->assertFalse(Csrf::validate(''));
    }

    public function testValidateSansSessionInitialisee(): void
    {
        // Pas de token en session → doit retourner false
        $this->assertFalse(Csrf::validate('n_importe_quoi'));
    }

    // ------------------------------------------------------------------
    // regenerate
    // ------------------------------------------------------------------

    public function testRegenerateChangeLToken(): void
    {
        $tokenAvant = Csrf::getToken();
        Csrf::regenerate();
        $tokenApres = Csrf::getToken();

        $this->assertNotSame($tokenAvant, $tokenApres);
    }

    // ------------------------------------------------------------------
    // field
    // ------------------------------------------------------------------

    public function testFieldRetourneUnInputHidden(): void
    {
        $html = Csrf::field();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('name="csrf_token"', $html);
    }

    public function testFieldContientLeToken(): void
    {
        $token = Csrf::getToken();
        $html  = Csrf::field();
        $this->assertStringContainsString($token, $html);
    }
}