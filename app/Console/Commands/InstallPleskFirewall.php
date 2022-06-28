<?php

namespace App\Console\Commands;

use App\Services\Vultr\Models\Server;
use Illuminate\Console\Command;
use Spatie\Ssh\Ssh;

class InstallPleskFirewall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:install-firewall
                            {--serverId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and Enable the Plesk Firewall';

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
        $server = Server::find($this->option('serverId'));
        $command = 'plesk installer add --components psa-firewall';

        $process = Ssh::create('root', $server->ip_address)->disableStrictHostKeyChecking()->execute($command);

        return Command::SUCCESS;
    }
}
