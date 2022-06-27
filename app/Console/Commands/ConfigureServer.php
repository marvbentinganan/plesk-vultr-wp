<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Services\Plesk\AdminClient;
use App\Services\Plesk\Client;
use App\Services\Plesk\Models\PleskInstance;
use App\Services\Vultr\Client as VultrClient;
use Illuminate\Console\Command;

class ConfigureServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:configure-server
                            {--domainId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure Plesk instance.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domain = Domain::find($this->option('domainId'));

        $customer = $domain->customer;
        $server = $domain->server;

        // Generate API Key
        if (!$server->plesk_instance()->exists()) {
            $pleskAdminClient = new AdminClient($server->ip_address, $server->default_password);
            $this->info('Generating Plesk API Key');
            $apiKey = $pleskAdminClient->createApiKey()->collect();

            $plesk = PleskInstance::create([
                'server_id' => $server->getKey(),
                'customer_id' => $customer->getKey(),
                'api_key' => $apiKey['key'],
            ]);

            $domain->update([
                'plesk_instance_id' => $plesk->getKey()
            ]);
        } else {
            $plesk = $server->plesk_instance;
        }

        $pleskClient = new Client($server->ip_address, $plesk->api_key);

        // Configure Panel URL and Login
        if ($plesk->custom_panel == false) {
            $this->info('Setting Up Panel');
            $initialize = $pleskClient->initialize($customer, config('services.plesk.panel_password'), $domain->panel);
            $this->info('Updating Panel Hostname');
            $pleskClient->setHostname($domain->panel);
            $plesk->update([
                'custom_panel' => true
            ]);
            $this->info('Hostname Updated');
            sleep(5);
        }

        // Add Domain to Plesk
        if ($plesk->attached_domain == false) {
            $this->info('Adding Domain to Panel');
            $addDomain = $pleskClient->addDomain($domain->name);
            $domain->update([
                'guid' => $addDomain->collect()['guid']
            ]);
            $plesk->update([
                'attached_domain' => true
            ]);
            $this->info("Domain {$domain->name} added to Plesk");
        }

        // Add SSL Certificate to domain
        if ($domain->ssl_certificate == false) {
            $this->info('Securing Site with SSL Certificate');
            $pleskClient->addDomainCertificate($customer->email, $domain->name);
            $domain->update([
                'ssl_certificate' => true
            ]);
            $this->info('Primary Domain secured with SSL Certificate');
        }

        // Add SSL Certificate to Plesk Panel
        if ($plesk->panel_certificate == false) {
            $this->info('Securing Panel with SSL Certificate');
            $pleskClient->enableKeepSecured();
            $pleskClient->setPanelCertificate($domain->panel, $customer->email);
            $plesk->update([
                'panel_certificate' => true
            ]);
            $this->info('SSL Certificate added to Panel');
        }

        // Install WordPress
        if ($plesk->wordpress_installed == false) {
            $this->info('Installing WordPress Site');
            $install = $pleskClient->installWordPress($customer->email, $domain->name);
            $plesk->update([
                'wordpress_installed' => true
            ]);
        }

        // Enable Caching
        if ($domain->caching_enabled == false) {
            $this->info('Enabling Nginx Cache');
            $pleskClient->enableCaching($domain->name);
            $domain->update([
                'caching_enabled' => true
            ]);
        }

        // Improve SSL Settings
        if ($domain->improved_ssl == false) {
            $this->info('Improving SSL Settings');
            $hsts = $pleskClient->enableHSTS($domain->name)->collect();
            $this->info("{$hsts['stdout']}");
            $ocsp = $pleskClient->enableOCSP($domain->name)->collect();
            $this->info("{$ocsp['stdout']}");

            $domain->update([
                'improved_ssl' => true
            ]);
        }

        // Update Reverse DNS
        if ($server->ipv4_reverse_dns == false) {
            $this->info('Updating Reverse DNS');
            $vultr = new VultrClient();
            $vultr->updateReverseDNS($server->provider_id, $server->ip_address, $domain->name);
            $server->update([
                'ipv4_reverse_dns' => true
            ]);

            $domain->update([
                'status' => 'active',
                'processed_at' => now()
            ]);
        }

        $this->info('Configuration Complete!');

        return Command::SUCCESS;
    }
}
