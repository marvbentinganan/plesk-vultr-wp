<?php

namespace App\Services\Plesk;

use Illuminate\Support\Facades\Http;

class AdminClient
{
    protected $client;
    protected $host;

    public function __construct(string $host, string $password)
    {
        $this->host = "https://{$host}:8443/api/v2";

        $this->client = Http::retry(10, 5)
        ->withOptions(['verify' => false])
        ->withBasicAuth('root', $password)
        ->acceptJson()
        ->withHeaders([
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Create a new API key for the Plesk instance
     *
     * @return Response
     */
    public function createApiKey()
    {
        return $this->client->post("{$this->host}/auth/keys", []);
    }
}
