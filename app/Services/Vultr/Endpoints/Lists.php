<?php

namespace App\Services\Vultr\Endpoints;

use App\Services\Vultr\Client;
use Illuminate\Support\Str;

class Lists extends Client
{
    public function applications(string $type = 'all')
    {
        return $this->client->get("{$this->apiUrl}/applications?type={$type}");
    }

    public function plans(string $type = 'all')
    {
        return $this->client->get("{$this->apiUrl}/plans?type={$type}");
    }

    public function regions()
    {
        return $this->client->get("{$this->apiUrl}/regions");
    }

    public function sshKeys()
    {
        return $this->client->get("{$this->apiUrl}/ssh-keys");
    }
}
