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
        try {
            $response = $this->httpClient->request('POST', $this->pistonApiUrl, [
                'auth_basic' => [$this->pistonApiUser, $this->pistonApiPassword],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $codeRequest,
                'verify_peer' => false,
                'verify_host' => false,
            ]);

            $result = $response->toArray();
            return $result;
        } catch (\Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface $e) {
            // Récupérer le détail de l'erreur de Piston
            $errorContent = $e->getResponse()->getContent(false);

            throw new \Exception("Erreur Piston (400): " . $errorContent . " | Requête envoyée: " . json_encode($codeRequest));
        }
    }
    public function createCodeRequest($code, $language): array
    {

        $languageConfig = [
            'python' => ['language' => 'python', 'version' => '3.10.0'],
            'javascript' => ['language' => 'node', 'version' => '18.15.0'], // Vraie version installée !
            'php' => ['language' => 'php', 'version' => '8.2.3'],
        ];

        // Récupérer la config ou utiliser le langage tel quel
        $config = $languageConfig[$language] ?? ['language' => $language, 'version' => '*'];

        // Ajouter les balises d'ouverture si nécessaire selon le langage
        if ($language === 'php' && !str_starts_with(trim($code), '<?php')) {
            $code = '<?php ' . $code;
        }

        $request = [
            'language' => $config['language'],
            'version' => $config['version'],
            'files' => [
                ['content' => $code]
            ]
        ];

        return $request;
    }
}
