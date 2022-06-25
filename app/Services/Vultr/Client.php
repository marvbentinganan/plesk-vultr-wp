<?php

namespace App\Services\Vultr;

use Illuminate\Support\Facades\Http;

class Client
{
    protected $client;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = Http::acceptJson()
        ->withHeaders(
            [
                'Authorization' => sprintf('%s %s', 'Bearer', config('services.vultr.api'))
            ]
        );
        $this->apiUrl = 'https://api.vultr.com/v2';
    }

    public function listInstances()
    {
        return $this->client->get("{$this->apiUrl}/instances")->object();
    }

    public function listApplications(string $type = 'all')
    {
        return $this->client->get("{$this->apiUrl}/applications?type={$type}")->object();
    }

    public function listPlans(string $type = 'all')
    {
        return $this->client->get("{$this->apiUrl}/plans?type={$type}")->object();
    }

    public function listRegions()
    {
        return $this->client->get("{$this->apiUrl}/regions")->object();
    }

    public function createInstance(int $appId = 31, string $region = 'lhr', string $plan = 'vc2-1c-2gb')
    {
        // Define defaults
        // App ID 31 is Plesk Web Admin
        // lhr is London
    }

    public function getInstance($instanceId)
    {
        return $this->client->get("{$this->apiUrl}/instances/{$instanceId}")->object();
    }
}
