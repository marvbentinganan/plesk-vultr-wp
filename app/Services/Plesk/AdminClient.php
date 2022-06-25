<?php

namespace App\Services\Plesk;

use Illuminate\Support\Facades\Http;

class AdminClient
{
    protected $client;
    protected $host;
    protected $apiKey;

    public function __construct(string $host, string $password)
    {
        $this->host = "https://{$host}:8443/api/v2/";

        $this->client = Http::withBasicAuth('root', $password)
        ->acceptJson();
    }

    public function createApiKey()
    {
        return $this->client->post("{$this->host}/auth/keys")->object();
    }
}
