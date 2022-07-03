<?php

namespace App\Console\Commands;

use App\Models\Domain;
use Illuminate\Console\Command;

class PendingDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:pending-domains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List Pending Domains';

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
        $domains = Domain::where('status', 'pending')->get(['domain_id', 'domain_uid', 'name', 'status']);

        $this->table(['ID', 'UUID', 'Name', 'Status'], $domains->toArray());

        return Command::SUCCESS;
    }
}
