<?php

namespace Alps\Bookmarker\Services;

use Illuminate\Config\Repository;

class PayloadHasher
{
    public function __construct(private Repository $config)
    {
    }

    public function createPayload(string $content, array $data): string
    {
        $json = json_encode([
            'content' => $content,
            'data' => $data,
        ]);

        return base64_encode($json);
    }

    public function calculateHash(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->config->get('app.key'));
    }

    public function verify(string $payload, string $hash): bool
    {
        return $hash === $this->calculateHash($payload);
    }

    public function parsePayload(string $payload): array
    {
        $jsonString = base64_decode($payload);

        if ($jsonString === false) {
            return [];
        }

        $parsed = json_decode($jsonString, true);

        return !is_array($parsed) ? [] : $parsed;
    }
}
