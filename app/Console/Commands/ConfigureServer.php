<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\PleskInstance;
use App\Models\Server;
use App\Services\Plesk\AdminClient;
use App\Services\Plesk\Client;
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
        dump($apiKey);

        $plesk = PleskInstance::create([
            'server_id' => $server->getKey(),
            'customer_id' => $customer->getKey(),
            'api_key' => $apiKey['key'],
            'temporary_domain' => sprintf('%s://%s:%s', 'https', $server->ip_address, 8443),
            'custom_domain' => sprintf('%s.%s', 'panel', $customer->domain)
        ]);

        // Connect Domain - domain.tld
        $pleskClient = new Client($server->ip_address, $plesk->api_key);


        // Setup SSL - panel.domain.tld

        return Command::SUCCESS;
    }
}
