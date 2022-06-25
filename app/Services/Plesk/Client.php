<?php

namespace App\Services\Plesk;

use Illuminate\Support\Facades\Http;

class Client
{
    protected $client;
    protected $domain;
    protected $apiKey;

    public function __construct(string $domain, string $apiKey)
    {
        $this->host = "https://{$domain}:8443/api/v2/";

        $this->client = Http::acceptJson()
        ->withHeaders(
            [
                'X-API-Key' => $apiKey
            ]
        );
    }
}
