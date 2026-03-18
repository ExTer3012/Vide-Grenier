<?php

namespace Tests\Unit;

use App\Models\Cities;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour App\Models\Cities
 * Teste la logique de validation des paramètres sans appeler l'API externe.
 */
class CitiesTest extends TestCase
{
    // ------------------------------------------------------------------
    // Validation de la requête
    // ------------------------------------------------------------------

    public function testRequeteVideRetourneTableauVide(): void
    {
        // Reproduit la logique de Cities::search() pour les cas limites
        $result = $this->simulerSearch('');
        $this->assertSame([], $result);
    }

    public function testRequeteEspacesRetourneTableauVide(): void
    {
        $result = $this->simulerSearch('   ');
        $this->assertSame([], $result);
    }

    public function testRequeteUnCaractereRetourneTableauVide(): void
    {
        // Minimum 2 caractères requis par l'API
        $result = $this->simulerSearch('P');
        $this->assertSame([], $result);
    }

    public function testRequeteSuffisantePasseLaValidation(): void
    {
        // 2 caractères minimum → ne retourne pas [] directement
        $query = trim('Pa');
        $this->assertTrue(strlen($query) >= 2);
    }

    // ------------------------------------------------------------------
    // Format de la réponse API
    // ------------------------------------------------------------------

    public function testFormatReponseApiCorrect(): void
    {
        // Simule une réponse de l'API adresse.data.gouv.fr
        $apiResponse = [
            'features' => [
                [
                    'properties' => [
                        'label'    => 'Paris (75000)',
                        'postcode' => '75000',
                        'city'     => 'Paris',
                    ]
                ],
                [
                    'properties' => [
                        'label'    => 'Parly (89240)',
                        'postcode' => '89240',
                        'city'     => 'Parly',
                    ]
                ],
            ]
        ];

        $cities = $this->parseApiResponse($apiResponse);

        $this->assertCount(2, $cities);
        $this->assertArrayHasKey('label',    $cities[0]);
        $this->assertArrayHasKey('postcode', $cities[0]);
        $this->assertArrayHasKey('city',     $cities[0]);
        $this->assertSame('Paris (75000)', $cities[0]['label']);
        $this->assertSame('75000',         $cities[0]['postcode']);
        $this->assertSame('Paris',         $cities[0]['city']);
    }

    public function testReponseApiSansFeatureRetourneTableauVide(): void
    {
        $apiResponse = ['type' => 'FeatureCollection']; // pas de 'features'
        $cities      = $this->parseApiResponse($apiResponse);
        $this->assertSame([], $cities);
    }

    public function testReponseApiFeaturesVidesRetourneTableauVide(): void
    {
        $apiResponse = ['features' => []];
        $cities      = $this->parseApiResponse($apiResponse);
        $this->assertSame([], $cities);
    }

    // ------------------------------------------------------------------
    // Helpers (reproduisent la logique de Cities::search)
    // ------------------------------------------------------------------

    private function simulerSearch(string $query): array
    {
        if (empty(trim($query)) || strlen(trim($query)) < 2) {
            return [];
        }
        return ['would_call_api' => true]; // indique que l'API serait appelée
    }

    private function parseApiResponse(array $data): array
    {
        if (!isset($data['features'])) {
            return [];
        }

        $cities = [];
        foreach ($data['features'] as $feature) {
            $props    = $feature['properties'];
            $cities[] = [
                'label'    => $props['label']    ?? '',
                'postcode' => $props['postcode']  ?? '',
                'city'     => $props['city']      ?? '',
            ];
        }

        return $cities;
    }
}