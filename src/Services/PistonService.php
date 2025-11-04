<?php

namespace App\Service;

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
    public function executeCode(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        $response = $this->httpClient->request('POST', $this->pistonApiUrl, [
            'auth_basic' => [$this->pistonApiUser, $this->pistonApiPassword],
            'json' => [
                'language' => $data['language'],
                'version' => '*',
                'files' => [
                    ['content' => $data['code']]
                ]
            ],
            'verify_peer' => false, // À retirer après 21h56 quand le SSL sera OK
        ]);

        return  $response->toArray();
    }
}
