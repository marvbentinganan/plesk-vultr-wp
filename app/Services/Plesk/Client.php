<?php

namespace App\Services\Plesk;

use App\Models\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Client
{
    protected $host;
    protected $client;

    public function __construct(string $domain, string $apiKey)
    {
        $this->host = "https://{$domain}:8443/api/v2";

        $this->client = Http::retry(10, 5)
        ->withOptions(['verify' => false])
        ->acceptJson()
        ->withHeaders(
            [
                'X-API-Key' => $apiKey
            ]
        );
    }

    public function initialize(Customer $customer, string $password = '~qRo3cB0Hbdu8zun')
    {
        $data = collect([
            'admin' => [
                'name' => $customer->name,
                'email' => $customer->email
            ],
            'password' => $password,
            'server_name' => sprintf('%s.%s', 'panel', $customer->domain)
        ])->toArray();

        return $this->client->post("{$this->host}/server/init", $data);
    }

    public function addDomain(Customer $customer)
    {
        $client = $this->listClients()->collect()->first();
        $guid = Str::uuid(32);
        $data = collect([
            'name' => $customer->domain,
            'description' => 'My website',
            'hosting_type' => 'virtual',
            'hosting_settings' => [
                'ftp_login' => 'ftpuser',
                'ftp_password' => '~qRo3cB0Hbdu8zun'
            ],
            'owner_client' => [
                'id' => $client['id'],
                'login' => $client['login'],
                'guid' => $client['guid'],
                'external_id' => $client['guid'],
            ],

        ])->toArray();

        return $this->client->post("{$this->host}/domains", $data);
    }

    public function addPanelCertificate()
    {
        $data = collect([
            'params' => [
                '--update',
                '-panel-certificate',
                'Lets Encrypt certificate'
            ]
        ])->toArray();

        return $this->client->post("{$this->host}/cli/server_pref/call", $data);
    }

    public function addDomainCertificate()
    {
    }

    public function listCommands()
    {
        return $this->client->get("{$this->host}/cli/commands");
    }

    public function listClients()
    {
        return $this->client->get("{$this->host}/clients");
    }

    public function listDomains()
    {
        return $this->client->get("{$this->host}/domains");
    }

    public function listExtensions()
    {
        return $this->client->get("{$this->host}/extensions");
    }
}
