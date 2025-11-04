<?php

namespace App\Service;

class JudgeService
{
    public function buildApiRequest(array $params): string
    {
        $language = $params['language'];
        $prompt = $params['prompt'];
        $apiKey = API_KEY;
        return http_build_query($params);
    }
}
