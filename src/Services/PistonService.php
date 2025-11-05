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
        ]);

        // Récupérer la réponse sous forme de tableau
        return $response->toArray();
    }

    public function createCodeRequest($code, $language): array
    {
        $request = [
            'language' => $language,
            'version' => '*',
            'files' => [
                ['content' => $code]
            ]
        ];

        return $request;
    }
}
