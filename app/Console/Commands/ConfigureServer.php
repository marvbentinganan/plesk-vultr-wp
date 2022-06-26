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
        $panel = $pleskClient->initialize($customer, config('services.plesk.panel_password'));
        if ($panel->successful()) {
            $this->info('Panel Initialized');
            $pleskClient->setHostname(sprintf('%s.%s', 'panel', $customer->domain));
            // Add Panel SSL Certificate
            $pleskClient->addPanelCertificate();
            $pleskClient->enableKeepSecured();
        }

        // Add Domain to Plesk
        $domain = $pleskClient->addDomain($customer);
        if ($domain->successful()) {
            $this->info("Domain {$customer->domain} added to Plesk");
            // Install WordPress
            $install = $pleskClient->installWordPress($customer);

            if ($install->collect()['code'] == 0) {
                $pleskClient->enableCaching($customer);

                $certificate = $pleskClient->addDomainCertificate($customer);
                if ($certificate->collect()['code'] == 0) {
                    $hsts = $pleskClient->enableHSTS($customer)->collect();
                    $this->info("{$hsts['stdout']}");
                    $ocsp = $pleskClient->enableOCSP($customer)->collect();
                    $this->info("{$ocsp['stdout']}");
                }
            }
        }

        // Update Reverse DNS
        $vultr = new VultrClient();
        $vultr->updateReverseDNS($server->provider_id, $server->ip_address, $customer->domain);

        return Command::SUCCESS;
    }
}
