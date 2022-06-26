<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\PleskInstance;
use App\Models\Server;
use App\Services\Plesk\AdminClient;
use App\Services\Plesk\Client;
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
                            {--serverId=}
                            {--customerId=}';

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
        $customer = Customer::find($this->option('customerId'));
        $server = Server::find($this->option('serverId'));

        // Create Admin Account - use customer email
        $pleskAdminClient = new AdminClient($server->ip_address, $server->default_password);

        $this->info('Generating Plesk API Key');
        $apiKey = $pleskAdminClient->createApiKey()->collect();

        $plesk = PleskInstance::create([
            'server_id' => $server->getKey(),
            'customer_id' => $customer->getKey(),
            'api_key' => $apiKey['key'],
            'temporary_domain' => sprintf('%s://%s:%s', 'https', $server->ip_address, 8443),
            'custom_domain' => sprintf('%s.%s', 'panel', $customer->domain)
        ]);

        $pleskClient = new Client($server->ip_address, $plesk->api_key);

        // Configure Panel URL and Login
        $this->info('Setting Up Panel');
        $panel = $pleskClient->initialize($customer, config('services.plesk.panel_password'));
        if ($panel->successful()) {
            $this->info('Updating Panel Hostname');
            $pleskClient->setHostname(sprintf('%s.%s', 'panel', $customer->domain));
            $this->info('Hostname Updated');
            // Add Panel SSL Certificate
            $this->info('Securing Panel with SSL Certificate');
            $pleskClient->addPanelCertificate();
            $pleskClient->enableKeepSecured();
            $this->info('SSL Certificate added to Panel');
        }

        // Add Domain to Plesk
        $this->info('Adding Domain to Panel');
        $domain = $pleskClient->addDomain($customer);
        if ($domain->successful()) {
            $this->info("Domain {$customer->domain} added to Plesk");
            // Add SSL Certificate to domain
            $this->info('Securing Site with SSL Certificate');
            $certificate = $pleskClient->addDomainCertificate($customer);

            if ($certificate->collect()['code'] == 0) {
                // Improve SSL Settings
                $this->info('Improving SSL Settings');
                $hsts = $pleskClient->enableHSTS($customer)->collect();
                $this->info("{$hsts['stdout']}");
                $ocsp = $pleskClient->enableOCSP($customer)->collect();
                $this->info("{$ocsp['stdout']}");
            }

            // Install WordPress
            $this->info('Installing WordPress Site');
            $install = $pleskClient->installWordPress($customer);

            if ($install->collect()['code'] == 0) {
                // Enable Caching
                $this->info('Enabling Nginx Cache');
                $pleskClient->enableCaching($customer);
            }
        }

        // Update Reverse DNS
        $this->info('Updating Reverse DNS');
        $vultr = new VultrClient();
        $vultr->updateReverseDNS($server->provider_id, $server->ip_address, $customer->domain);

        $this->info('Configuration Complete!');

        return Command::SUCCESS;
    }
}
