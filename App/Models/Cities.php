<?php

namespace App\Models;

use Core\Model;
use Exception;

/**
 * Modèle Cities
 * Recherche de villes via l'API adresse.data.gouv.fr
 * (plus de dépendance à la table villes_france)
 */
class Cities extends Model
{
    private const API_URL = 'https://api-adresse.data.gouv.fr/search/';

    /**
     * Recherche des villes correspondant à la saisie utilisateur.
     * Utilise l'API officielle adresse.data.gouv.fr
     *
     * @param string $query Saisie de l'utilisateur
     * @return array Liste de ['label' => '...', 'postcode' => '...', 'city' => '...']
     */
    public static function search(string $query): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $url = self::API_URL . '?' . http_build_query([
                'q'     => $query,
                'type'  => 'municipality',
                'limit' => 10,
            ]);

        $context = stream_context_create([
            'http' => [
                'timeout'     => 3,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            \Core\Error::log('WARNING', "API adresse.data.gouv.fr inaccessible pour la requête : {$query}");
            return [];
        }

        $data = json_decode($response, true);

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