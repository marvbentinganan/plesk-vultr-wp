<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateWebsite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:create-website';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a wordpress site to your Plesk instance.';

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
