<?php

namespace App\Services\Vultr;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
        return $this->client->get("{$this->apiUrl}/instances");
    }

    public function listApplications(string $type = 'all')
    {
        return $this->client->get("{$this->apiUrl}/applications?type={$type}");
    }

    public function listPlans(string $type = 'all')
    {
        return $this->client->get("{$this->apiUrl}/plans?type={$type}");
    }

    public function listRegions()
    {
        return $this->client->get("{$this->apiUrl}/regions");
    }

    public function listSshKeys()
    {
        return $this->client->get("{$this->apiUrl}/ssh-keys");
    }

    public function createInstance(int $appId = 31, string $region = 'lhr', string $plan = 'vc2-1c-2gb')
    {
        // Define defaults
        // App ID 31 is Plesk Web Admin
        // lhr is London
        $data = collect([
            'region' => $region,
            'plan' => $plan,
            'app_id' => $appId,
            'activation_email' => true,
            'sshkey_id' => ['17cdbce3-d562-4227-a1e8-a3f90b875396'],
            'backups' => 'enabled',
            'hostname' => sprintf('%s-%s-%s-%s', 'vtr', $region, 'web', Str::random(5)),
            'label' => sprintf('%s-%s-%s-%s', 'vtr', $region, 'web', Str::random(5))
        ])->toArray();

        return $this->client->post("{$this->apiUrl}/instances", $data);
    }

    public function getInstance(string $instanceId)
    {
        return $this->client->get("{$this->apiUrl}/instances/{$instanceId}");
    }
}
