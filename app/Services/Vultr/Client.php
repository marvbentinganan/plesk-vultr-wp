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
        $this->apiUrl = config('services.vultr.api_endpoint');
    }

    public function getInstances()
    {
        return $this->client->get("{$this->apiUrl}/instances");
    }

    public function getApplications(string $type = 'all')
    {
        return $this->client->get("{$this->apiUrl}/applications?type={$type}");
    }

    public function getPlans(string $type = 'all')
    {
        return $this->client->get("{$this->apiUrl}/plans?type={$type}");
    }

    public function getRegions()
    {
        return $this->client->get("{$this->apiUrl}/regions");
    }

    public function getSshKeys()
    {
        return $this->client->get("{$this->apiUrl}/ssh-keys");
    }

    public function createInstance(int $appId, string $region, string $plan)
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
            'hostname' => sprintf('%s-%s-%s-%s', 'vtr', $region, 'web', Str::random(3)),
            'label' => sprintf('%s-%s-%s-%s', 'vtr', $region, 'web', Str::random(3))
        ])->toArray();

        return $this->client->post("{$this->apiUrl}/instances", $data);
    }

    public function deleteInstance(string $instanceId)
    {
        return $this->client->delete("{$this->apiUrl}/instances/{$instanceId}");
    }

    public function reinstallInstance(string $instanceId)
    {
        return $this->client->get("{$this->apiUrl}/instances/{$instanceId}/reinstall");
    }

    public function getInstance(string $instanceId)
    {
        return $this->client->get("{$this->apiUrl}/instances/{$instanceId}");
    }

    public function updateReverseDNS(string $instanceId, string $ipAddress, string $domain)
    {
        $data = collect([
            'ip' => $ipAddress,
            'reverse' => $domain
        ])->toArray();

        return $this->client->post("{$this->apiUrl}/instances/{$instanceId}/ipv4/reverse", $data);
    }
}
