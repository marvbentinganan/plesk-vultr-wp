<?php

namespace App\Console\Commands;

use App\Services\Vultr\Client;
use Illuminate\Console\Command;

class ProvisionServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:create-server';

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

        $server = $client->createInstance();

        // Create Server record

        // Do While loop here, if status is active,
        // Update server record
        // Start vp-configure-server command

        return Command::SUCCESS;
    }
}
