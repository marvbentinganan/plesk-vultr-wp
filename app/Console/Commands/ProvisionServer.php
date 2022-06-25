<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\Vultr\Client;
use Illuminate\Console\Command;

class ProvisionServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:create-server
                            {--customerId=}';

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
        $client = new Client();

        $response = $client->createInstance();

        // Create Server record
        $instance = $response->collect()['instance'];

        $server = Server::create([
            'customer_id' => $this->option('customerId'),
            'provider_id' => $instance['id'],
            'default_password' => $instance['default_password'],
            'hostname' => $instance['hostname'],
            'ip_address' => $instance['main_ip'],
            'plan' => $instance['plan'],
            'region' => $instance['region'],
            'status' => $instance['status']
        ]);

        // Do While loop here, if status is active,
        do {
            $response = $client->getInstance($server->provider_id);
            if ($response->successful()) {
                $instance = $response->collect()['instance'];
                $status = $instance['status'];
            } else {
                $status = 'pending';
            }
            $this->info('Server is still provisioning...');
            // Pause for 2 minutes before checking again
            sleep(120);
        } while ($status != 'active');

        $this->info('Done Provisioning. Updating local server record.');
        // Update server record
        $server->update([
            'status' => $instance['status'],
            'ip_address' => $instance['main_ip']
        ]);

        $this->info("Server created with IP Address: {$server->ip_address}");

        // Start vp-configure-server command

        return Command::SUCCESS;
    }
}
