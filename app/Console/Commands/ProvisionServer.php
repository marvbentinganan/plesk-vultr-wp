<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Services\Vultr\Client;
use App\Services\Vultr\Models\Server;
use Illuminate\Console\Command;

class ProvisionServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:create-server
                            {--domainId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new instance in Vultr with Plesk installed.';

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

        $client = new Client();

        $response = $client->createInstance();

        // Create Server record
        $instance = $response->collect()['instance'];

        $server = Server::create([
            'customer_id' => $customer->getKey(),
            'provider_id' => $instance['id'],
            'default_password' => $instance['default_password'],
            'hostname' => $instance['hostname'],
            'ip_address' => $instance['main_ip'],
            'plan' => $instance['plan'],
            'region' => $instance['region'],
            'status' => $instance['status']
        ]);

        // Update domain record
        $domain->update([
            'server_id' => $server->getKey()
        ]);


        // Do While loop here, to check if server is done provisioning
        do {
            $response = $client->getInstance($server->provider_id);
            $instance = $response->collect()['instance'];
            $status = $instance['status'];

            $this->line('Server is still provisioning...');
            // Pause for a minute before checking again
            sleep(60);
        } while ($status != 'active');

        $this->info('Done Provisioning. Updating local server record.');

        // Update server record
        $server->update([
            'status' => $instance['status'],
            'ip_address' => $instance['main_ip']
        ]);

        $this->info("Server created with IP Address: {$server->ip_address}");

        // Update DNS Records
        //$this->call('vp:update-dns', ['--domainId' => $domain->getKey(), '--ipAddress' => $server->ip_address]);

        // Configure the Server
        //$this->call('vp:configure-server', ['--domainId' => $domain->getKey()]);

        return Command::SUCCESS;
    }
}
