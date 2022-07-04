<?php

namespace App\Services\WooCommerce\Commands;

use App\Services\WooCommerce\Client;
use App\Services\WooCommerce\Models\WooCommerceOrder;
use Illuminate\Console\Command;

class ImportOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wc:import-paid-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Orders from WooCommerce API';

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
        $woocommerce = new Client();

        $orders = $woocommerce->getPaidOrders()->collect();

        $orders->each(function ($order) {
            WooCommerceOrder::create([
                'source_order_id' => $order['id'],
                'data' => collect($order)
            ]);
        });

        return Command::SUCCESS;
    }
}
