<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:create-customer
                            {--domain=}
                            {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $customer = Customer::create([
            'email' => $this->option('email'),
            'domain' => $this->option('domain')
        ]);

        if ($customer) {
            Artisan::call('vp:create-server', ['--customerId' => $customer->getKey()]);
        }

        return Command::SUCCESS;
    }
}
