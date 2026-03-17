<?php

namespace Tests\Unit;

use App\Utility\Hash;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour App\Utility\Hash
 */
class HashTest extends TestCase
{
    // ------------------------------------------------------------------
    // hashPassword / verifyPassword
    // ------------------------------------------------------------------

    public function testHashPasswordRetourneUneChaineNonVide(): void
    {
        $hash = Hash::hashPassword('monMotDePasse');
        $this->assertNotEmpty($hash);
        $this->assertIsString($hash);
    }

    public function testHashPasswordUtiliseArgon2id(): void
    {
        $hash = Hash::hashPassword('monMotDePasse');
        $this->assertStringStartsWith('$argon2id$', $hash);
    }

    public function testDeuxHashsDuMemeMdpSontDifferents(): void
    {
        // Argon2id génère un salt aléatoire à chaque appel
        $hash1 = Hash::hashPassword('monMotDePasse');
        $hash2 = Hash::hashPassword('monMotDePasse');
        $this->assertNotSame($hash1, $hash2);
    }

    public function testVerifyPasswordAvecBonMotDePasse(): void
    {
        $hash = Hash::hashPassword('monMotDePasse');
        $this->assertTrue(Hash::verifyPassword('monMotDePasse', $hash));
    }

    public function testVerifyPasswordAvecMauvaisMotDePasse(): void
    {
        $hash = Hash::hashPassword('monMotDePasse');
        $this->assertFalse(Hash::verifyPassword('mauvaisMotDePasse', $hash));
    }

    public function testVerifyPasswordAvecChaineVide(): void
    {
        $hash = Hash::hashPassword('monMotDePasse');
        $this->assertFalse(Hash::verifyPassword('', $hash));
    }

    // ------------------------------------------------------------------
    // needsRehash
    // ------------------------------------------------------------------

    public function testNeedsRehashRetourneFalsePourHashValide(): void
    {
        $hash = Hash::hashPassword('monMotDePasse');
        $this->assertFalse(Hash::needsRehash($hash));
    }

    public function testNeedsRehashRetourneTruePourAncienHash(): void
    {
        // Simule un ancien hash MD5 (algo différent → doit être rehashé)
        $ancienHash = md5('monMotDePasse');
        $this->assertTrue(Hash::needsRehash($ancienHash));
    }

    // ------------------------------------------------------------------
    // generateToken
    // ------------------------------------------------------------------

    public function testGenerateTokenRetourneUneChaineHex(): void
    {
        $token = Hash::generateToken();
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
    }

    public function testGenerateTokenLongueurParDefaut(): void
    {
        // 32 octets → 64 caractères hexadécimaux
        $token = Hash::generateToken(32);
        $this->assertSame(64, strlen($token));
    }

    public function testDeuxTokensSontDifferents(): void
    {
        $this->assertNotSame(Hash::generateToken(), Hash::generateToken());
    }
}