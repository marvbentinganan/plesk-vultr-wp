<?php

namespace App\Services\Plesk;

use App\Models\Customer;
use App\Models\Domain;
use Illuminate\Support\Facades\Http;

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

    /**
     * Setup Administrator user and Custom Panel Domain
     *
     * @param Customer $customer
     * @param string $password
     * @return Response
     */
    public function initialize(Customer $customer, string $password, string $panel)
    {
        $data = collect([
            'admin' => [
                'name' => $customer->name,
                'email' => $customer->email
            ],
            'password' => $password,
            'server_name' => $panel
        ])->toArray();

        return $this->client->post("{$this->host}/server/init", $data);
    }

    public function setHostname($hostname)
    {
        $data = collect([
            'params' => [
                '--set',
                sprintf('%s=%s', 'FullHostName', $hostname)
            ],
        ])->toArray();

        return $this->client->post("{$this->host}/cli/settings/call", $data);
    }

    /**
     * Add Domain to Plesk instance
     *
     * @param Customer $customer
     * @return Response
     */
    public function addDomain(string $domain)
    {
        $client = $this->getClients()->collect()->first();
        $data = collect([
            'name' => $domain,
            'description' => 'My website',
            'hosting_type' => 'virtual',
            'hosting_settings' => [
                'ftp_login' => 'ftpuser',
                'ftp_password' => config('services.plesk.panel_password')
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

    public function installWordPress(string $email, string $domain)
    {
        $data = collect([
            'params' => [
                '--call',
                'wp-toolkit',
                '--install',
                '-domain-name',
                $domain,
                '-admin-email',
                $email
            ],
            'env' => [
                'ADMIN_PASSWORD' => config('services.plesk.wordpress_password')
            ]
        ])->toArray();

        return $this->client->post("{$this->host}/cli/extension/call", $data);
    }

    public function enableCaching(string $domain)
    {
        $data = collect([
            'params' => [
            '--update-web-server-settings',
            $domain,
            '-nginx-cache-enabled',
            'true',
            '-nginx-cache-timeout',
            '60',
            '-nginx-cache-key',
            sprintf('%s%s%s', "'", '$scheme$request_method$host$request_uri', "'"),
            '-nginx-cache-bypass-locations',
            "'/wp-admin/'"
            ]
        ])->toArray();

        return $this->client->post("{$this->host}/cli/subscription/call", $data);
    }

    /**
     * Add Let's Encrypt SSL Certificate to Plesk Panel
     *
     * @return Response
     */
    public function addPanelCertificate(string $domain, string $email)
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

    public function addDomainCertificate(string $email, string $domain)
    {
        $data = collect([
            'params' => [
            '--call',
            'sslit',
            '--certificate',
            '-issue',
            '-domain',
            $domain,
            '-registrationEmail',
            $email,
            '-secure-domain',
            '-secure-www',
            '-secure-webmail',
            '-secure-mail'
            ]
        ])->toArray();

        return $this->client->post("{$this->host}/cli/extension/call", $data);
    }

    /**
     * Enable Keep Plesk Secured
     *
     * @return void
     */
    public function enableKeepSecured()
    {
        $data = collect([
            'params' => [
            '--call',
            'sslit',
            '--panel-keep-secured',
            '-enable'
            ]
        ])->toArray();

        return $this->client->post("{$this->host}/cli/extension/call", $data);
    }

    /**
     * Enable HSTS
     *
     * @param Customer $customer
     * @return Response
     */
    public function enableHSTS(string $domain)
    {
        $data = collect([
            'params' => [
            '--call',
            'sslit',
            '--hsts',
            '-enable',
            '-domain',
            $domain,
            ]
        ])->toArray();

        return $this->client->post("{$this->host}/cli/extension/call", $data);
    }

    /**
     * Enable OCSP Stapling
     *
     * @param Customer $customer
     * @return Response
     */
    public function enableOCSP(string $domain)
    {
        $data = collect([
            'params' => [
            '--call',
            'sslit',
            '--ocsp-stapling',
            '-enable',
            '-domain',
            $domain,
            ]
        ])->toArray();

        return $this->client->post("{$this->host}/cli/extension/call", $data);
    }

    /**
     * Get List of available CLI Commands
     *
     * @return Response
     */
    public function getCommands()
    {
        return $this->client->get("{$this->host}/cli/commands");
    }

    /**
     * Get List of Clients for the Plesk Instance
     *
     * @return Response
     */
    public function getClients()
    {
        return $this->client->get("{$this->host}/clients");
    }

    /**
     * Get List of Domains added to Plesk Instance
     *
     * @return void
     */
    public function getDomains()
    {
        return $this->client->get("{$this->host}/domains");
    }

    /**
     * Get List of available extensions
     *
     * @return Response
     */
    public function getExtensions()
    {
        return $this->client->get("{$this->host}/extensions");
    }
}
