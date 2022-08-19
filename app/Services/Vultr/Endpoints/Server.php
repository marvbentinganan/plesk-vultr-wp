<?php

namespace App\Services\Vultr\Endpoints;

use App\Services\Vultr\Client;
use Illuminate\Support\Str;

class Server extends Client
{
    public function create(array $data)
    {
        return $this->client->post("{$this->apiUrl}/instances", $data);
    }

    public function list()
    {
        return $this->client->get("{$this->apiUrl}/instances");
    }

    public function get(string $instanceId)
    {
        return $this->client->get("{$this->apiUrl}/instances/{$instanceId}");
    }

    public function delete(string $instanceId)
    {
        return $this->client->delete("{$this->apiUrl}/instances/{$instanceId}");
    }

    public function reinstall(string $instanceId)
    {
        return $this->client->get("{$this->apiUrl}/instances/{$instanceId}/reinstall");
    }
}
