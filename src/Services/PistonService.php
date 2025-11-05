<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Attribute\Route;

class PistonService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $pistonApiUrl,
        private string $pistonApiUser,
        private string $pistonApiPassword
    ) {}

    #[Route('/api/execute-code', methods: ['POST'])]
    public function controlCodeWithPiston(array $codeRequest): array
    {
        $response = $this->httpClient->request('POST', $this->pistonApiUrl, [
            'auth_basic' => [$this->pistonApiUser, $this->pistonApiPassword],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $codeRequest,
            'verify_peer' => false, // À retirer quand le SSL sera OK
            'verify_host' => false, // À retirer quand le SSL sera OK
        ]);

        // Récupérer la réponse sous forme de tableau
        return $response->toArray();
    }

    public function createCodeRequest($code, $language): array
    {
        // TEMPORAIRE : forcer PHP pour tester
        $language = 'php';
        $version = '8.2.3';

        /* Map des versions par défaut pour chaque langage
        $versions = [
            'python' => '3.10.0',
            'node' => '18.15.0',  // Pour JavaScript
            'php' => '8.2.3',
            'java' => '15.0.2',
            'rust' => '1.68.2',
            'go' => '1.16.2',
        ];

        $version = $versions[$language] ?? '*';
        */

        $request = [
            'language' => $language,
            'version' => $version,
            'files' => [
                ['content' => $code]
            ]
        ];

        return $request;
    }
}
