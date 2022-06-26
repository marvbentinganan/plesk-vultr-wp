<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateDNS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:update-dns
                            {--serverID=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add additional DNS Entries';

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
        return Command::SUCCESS;
    }
}
